<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Database backups written as plain SQL files in storage/app/backups.
 *
 * Pure PHP (no mysqldump needed). The file starts with a "-- FreshTrack Backup"
 * marker and statements are separated by a "-- @@" line, so a restore can check
 * every statement before it touches the database.
 */
class BackupService
{
    private const MARKER = '-- FreshTrack Backup';
    private const SEPARATOR = "\n-- @@\n";

    /** Runtime/bookkeeping tables that are neither backed up nor restored. */
    private const EXCLUDED = [
        'backups', 'app_settings', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens',
    ];

    private const SCHEDULE_DEFAULTS = ['daily' => true, 'weekly' => true, 'monthly' => false];

    // ------------------------------------------------------------------ backup

    public function create(string $type = 'manual', ?int $userId = null): Backup
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return $this->failed($type, $userId, 'Could not create the backups folder.');
        }

        $filename = 'freshtrack_' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(4)) . '.sql';
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        try {
            $this->dumpTo($path);
        } catch (Throwable $e) {
            @unlink($path);

            return $this->failed($type, $userId, $e->getMessage());
        }

        return Backup::create([
            'filename' => $filename,
            'size_bytes' => filesize($path),
            'type' => $type,
            'status' => 'success',
            'created_by' => $userId,
        ]);
    }

    private function failed(string $type, ?int $userId, string $message): Backup
    {
        return Backup::create([
            'filename' => '-',
            'size_bytes' => 0,
            'type' => $type,
            'status' => 'failed',
            'message' => $message,
            'created_by' => $userId,
        ]);
    }

    private function dumpTo(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $out = fopen($path, 'wb');
        if (!$out) {
            throw new RuntimeException('Could not write the backup file.');
        }

        $write = fn (string $sql) => fwrite($out, $sql . self::SEPARATOR);

        fwrite($out, self::MARKER . "\n-- Generated: " . now()->toDateTimeString() . ' (' . config('app.timezone') . ")\n-- Database: " . DB::getDatabaseName() . "\n");
        fwrite($out, self::SEPARATOR);
        $write('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->tables() as $table) {
            $create = (array) DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $write("DROP TABLE IF EXISTS `{$table}`");
            $write(rtrim($create['Create Table'] ?? array_values($create)[1]));

            $columns = null;
            $batch = [];
            foreach (DB::table($table)->cursor() as $row) {
                $row = (array) $row;
                $columns ??= '`' . implode('`, `', array_keys($row)) . '`';
                $batch[] = '(' . implode(', ', array_map(fn ($v) => $this->literal($pdo, $v), $row)) . ')';

                if (count($batch) === 200) {
                    $write("INSERT INTO `{$table}` ({$columns}) VALUES\n" . implode(",\n", $batch));
                    $batch = [];
                }
            }
            if ($batch) {
                $write("INSERT INTO `{$table}` ({$columns}) VALUES\n" . implode(",\n", $batch));
            }
        }

        $write('SET FOREIGN_KEY_CHECKS=1');
        fclose($out);
    }

    private function tables(): array
    {
        $rows = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

        return collect($rows)
            ->map(fn ($r) => array_values((array) $r)[0])
            ->reject(fn ($t) => in_array($t, self::EXCLUDED, true))
            ->values()
            ->all();
    }

    private function literal(\PDO $pdo, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    // ----------------------------------------------------------------- restore

    /**
     * Replace the database with the contents of a backup file. A safety backup of
     * the current data is taken first and put back automatically if the restore fails.
     */
    public function restore(string $path, ?int $userId = null): Backup
    {
        $statements = $this->readStatements($path);

        $safety = $this->create('pre-restore', $userId);
        if ($safety->status !== 'success') {
            throw new RuntimeException('Could not take a safety backup first: ' . $safety->message);
        }

        try {
            $this->execute($statements);
        } catch (Throwable $e) {
            try {
                $this->execute($this->readStatements($safety->path));
            } catch (Throwable) {
                // nothing more we can do; report the original error below
            }

            throw new RuntimeException('Restore failed and the previous data was put back: ' . $e->getMessage());
        }

        return $safety;
    }

    /** @return string[] validated SQL statements */
    private function readStatements(string $path): array
    {
        $contents = @file_get_contents($path);
        if ($contents === false || !str_starts_with($contents, self::MARKER)) {
            throw new RuntimeException('This is not a FreshTrack backup file.');
        }

        $statements = array_values(array_filter(
            array_map('trim', explode(self::SEPARATOR, $contents)),
            fn ($s) => $s !== '' && !str_starts_with($s, '--')
        ));

        if (!$statements) {
            throw new RuntimeException('The backup file is empty.');
        }

        foreach ($statements as $sql) {
            if (!preg_match('/^(?:SET FOREIGN_KEY_CHECKS=[01]$|(?:DROP TABLE IF EXISTS|CREATE TABLE|INSERT INTO) `([A-Za-z0-9_]+)`)/', $sql, $m)) {
                throw new RuntimeException('The backup file contains an unexpected statement and was not restored.');
            }
            if (isset($m[1]) && in_array($m[1], self::EXCLUDED, true)) {
                throw new RuntimeException("The backup file touches a protected table ({$m[1]}) and was not restored.");
            }
        }

        return $statements;
    }

    private function execute(array $statements): void
    {
        try {
            foreach ($statements as $sql) {
                DB::unprepared($sql);
            }
        } finally {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    // ---------------------------------------------------------------- schedule

    public function schedule(): array
    {
        $saved = DB::table('app_settings')->where('key', 'like', 'backup_%')->pluck('value', 'key');

        $out = [];
        foreach (self::SCHEDULE_DEFAULTS as $name => $default) {
            $out[$name] = isset($saved["backup_{$name}"]) ? $saved["backup_{$name}"] === '1' : $default;
        }

        return $out;
    }

    public function setSchedule(string $name, bool $enabled): void
    {
        abort_unless(array_key_exists($name, self::SCHEDULE_DEFAULTS), 422);

        DB::table('app_settings')->updateOrInsert(
            ['key' => "backup_{$name}"],
            ['value' => $enabled ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function scheduleEnabled(string $name): bool
    {
        return $this->schedule()[$name] ?? false;
    }

    /** Keep the newest $keep automatic backups; older files and rows are deleted. */
    public function prune(int $keep = 30): void
    {
        Backup::where('type', 'automatic')->orderByDesc('id')->skip($keep)->take(PHP_INT_MAX)->get()
            ->each(function (Backup $b) {
                @unlink($b->path);
                $b->delete();
            });
    }
}
