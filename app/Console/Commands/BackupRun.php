<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupRun extends Command
{
    protected $signature = 'backup:run
                            {--type=automatic : manual | automatic}';

    protected $description = 'Create a database backup in storage/app/backups';

    public function handle(BackupService $backups): int
    {
        $type = $this->option('type') === 'manual' ? 'manual' : 'automatic';

        // Daily, weekly and monthly schedules can fire together; one backup is enough
        if ($type === 'automatic' && Backup::where('type', 'automatic')->where('status', 'success')
            ->where('created_at', '>=', now()->subMinutes(30))->exists()) {
            $this->info('An automatic backup was just made; skipping.');
            return self::SUCCESS;
        }

        $backup = $backups->create($type);

        if ($backup->status !== 'success') {
            $this->error('Backup failed: ' . $backup->message);
            return self::FAILURE;
        }

        if ($type === 'automatic') {
            $backups->prune();
        }

        $this->info("Backup saved: {$backup->filename} ({$backup->size_label})");
        return self::SUCCESS;
    }
}
