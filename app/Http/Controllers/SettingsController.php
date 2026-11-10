<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\OwnerSetting;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private const NOTIFICATION_KEYS = [
        'email_notifications', 'sms_notifications', 'push_notifications',
        'low_stock_alerts', 'spoilage_alerts', 'forecast_alerts',
    ];

    public function page(BackupService $backups)
    {
        $user = Auth::user();
        $settings = OwnerSetting::forUser($user->id);
        $biz = DB::table('business_profiles')->orderBy('id')->first();

        [$first, $last] = $this->splitName($user->name);

        $isOwner = $user->role === 'owner';
        $data = [
            'user' => $user,
            'isOwner' => $isOwner,
            'profile' => [
                'first_name' => $first,
                'last_name' => $last,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'business_name' => $biz->business_name ?? 'FreshTrack ' . config('services.openweather.city'),
                'location' => $biz->location ?? config('services.openweather.city') . ', Philippines',
            ],
            'businessName' => $biz->business_name ?? 'FreshTrack ' . config('services.openweather.city'),
            'accountCreated' => $settings->formatDate($user->created_at),
            'notif' => $settings->only(self::NOTIFICATION_KEYS),
            'appearance' => $settings->appearance(),
            'avatarUrl' => $user->avatarUrl(),
            'themes' => OwnerSetting::THEMES,
            'region' => $settings->only(['language', 'date_format', 'currency', 'timezone']),
            'regionOptions' => [
                'language' => OwnerSetting::LANGUAGES,
                'date_format' => OwnerSetting::DATE_FORMATS,
                'currency' => OwnerSetting::CURRENCIES,
                'timezone' => OwnerSetting::TIMEZONES,
            ],
            'system' => [
                'database' => $this->databaseLabel(),
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
            ],
            'backup' => null,
        ];

        if ($isOwner) {
            $data['backup'] = $this->backupPayload($backups, $settings);
        }

        return view('pages.settings', $data);
    }

    // ----------------------------------------------------------------- profile

    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name' => 'nullable|string|max:60',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]*$/'],
            'business_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ], [
            'phone.regex' => 'The phone number may only contain digits, spaces, + ( ) and -.',
        ]);

        $name = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));

        $user->update([
            'name' => $name,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // The business profile belongs to the owner account
        if ($user->role === 'owner') {
            DB::table('business_profiles')->updateOrInsert(
                ['owner_user_id' => $user->id],
                [
                    'business_name' => $validated['business_name'] ?: 'FreshTrack ' . config('services.openweather.city'),
                    'owner_name' => $name,
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'location' => $validated['location'] ?? null,
                    'role' => 'Owner',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'name' => $user->name,
            'initials' => collect(preg_split('/\s+/', $user->name))->filter()->take(2)
                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''),
        ]);
    }

    // ----------------------------------------------------------- notifications

    public function updateNotification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', Rule::in(self::NOTIFICATION_KEYS)],
            'value' => 'required|boolean',
        ]);

        $settings = OwnerSetting::forUser(Auth::id());
        $settings->update([$validated['key'] => (bool) $validated['value']]);

        return response()->json(['success' => true, 'key' => $validated['key'], 'value' => (bool) $validated['value']]);
    }

    // ------------------------------------------------------- language & region

    /** Upload / replace the signed-in user's profile picture. */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.mimes' => 'Please choose a JPG, PNG or WEBP image.',
            'avatar.max' => 'The picture must be 2 MB or smaller.',
        ]);

        $user = Auth::user();
        $old = $user->avatar_path;
        $path = $request->file('avatar')->store('avatars');
        $user->forceFill(['avatar_path' => $path])->save();

        if ($old) {
            Storage::delete($old);
        }

        return response()->json(['success' => true, 'message' => 'Profile picture updated.', 'url' => $user->avatarUrl()]);
    }

    public function removeAvatar(): JsonResponse
    {
        $user = Auth::user();
        if ($user->avatar_path) {
            Storage::delete($user->avatar_path);
            $user->forceFill(['avatar_path' => null])->save();
        }

        return response()->json(['success' => true, 'message' => 'Profile picture removed.', 'url' => null]);
    }

    /** Streams a user's profile picture (stored privately, so it needs a signed-in session). */
    public function avatar(\App\Models\User $user)
    {
        abort_unless($user->avatar_path && Storage::exists($user->avatar_path), 404);

        return Storage::response($user->avatar_path, null, ['Cache-Control' => 'private, max-age=86400']);
    }

    public function updateAppearance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dark_mode' => ['sometimes', 'boolean'],
            'color_theme' => ['sometimes', Rule::in(array_keys(OwnerSetting::THEMES))],
            'font_size' => ['sometimes', Rule::in(array_keys(OwnerSetting::FONT_SIZES))],
        ]);

        $settings = OwnerSetting::forUser(Auth::id());
        $settings->update($validated);

        return response()->json(['success' => true, 'appearance' => $settings->refresh()->appearance()]);
    }

    public function updateRegion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => ['required', Rule::in(array_keys(OwnerSetting::LANGUAGES))],
            'date_format' => ['required', Rule::in(array_keys(OwnerSetting::DATE_FORMATS))],
            'currency' => ['required', Rule::in(array_keys(OwnerSetting::CURRENCIES))],
            'timezone' => ['required', Rule::in(array_keys(OwnerSetting::TIMEZONES))],
        ]);

        $settings = OwnerSetting::forUser(Auth::id());
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Language & region settings saved.',
            'today' => $settings->formatDate(now()),
        ]);
    }

    // ------------------------------------------------------------------ backups

    public function backups(BackupService $backups): JsonResponse
    {
        return response()->json($this->backupPayload($backups, OwnerSetting::forUser(Auth::id())));
    }

    public function createBackup(BackupService $backups): JsonResponse
    {
        set_time_limit(300);
        $backup = $backups->create('manual', Auth::id());

        if ($backup->status !== 'success') {
            return response()->json(['success' => false, 'message' => 'Backup failed: ' . $backup->message]
                + $this->backupPayload($backups, OwnerSetting::forUser(Auth::id())), 500);
        }

        return response()->json(['success' => true, 'message' => 'Backup created.']
            + $this->backupPayload($backups, OwnerSetting::forUser(Auth::id())));
    }

    public function downloadBackup(Backup $backup)
    {
        abort_unless($backup->status === 'success' && is_file($backup->path), 404, 'Backup file not found.');

        return response()->download($backup->path, $backup->filename, ['Content-Type' => 'application/sql']);
    }

    public function restoreBackup(Backup $backup, BackupService $backups): JsonResponse
    {
        if ($backup->status !== 'success' || !is_file($backup->path)) {
            return response()->json(['success' => false, 'message' => 'Backup file not found.'], 404);
        }

        return $this->runRestore($backups, $backup->path);
    }

    public function restoreUpload(Request $request, BackupService $backups): JsonResponse
    {
        $request->validate(['backup' => 'required|file|max:204800']);

        $file = $request->file('backup');
        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return response()->json(['success' => false, 'message' => 'Please choose a .sql backup file.'], 422);
        }

        return $this->runRestore($backups, $file->getRealPath());
    }

    public function updateSchedule(Request $request, BackupService $backups): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'enabled' => 'required|boolean',
        ]);

        $backups->setSchedule($validated['name'], (bool) $validated['enabled']);

        return response()->json(['success' => true, 'schedule' => $backups->schedule()]);
    }

    private function runRestore(BackupService $backups, string $path): JsonResponse
    {
        set_time_limit(600);

        try {
            $backups->restore($path, Auth::id());
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // The restored data may not contain the current user; the page reload will sort that out
        return response()->json(['success' => true, 'message' => 'Backup restored. A safety copy of the previous data was saved to the history.']);
    }

    // ----------------------------------------------------------------- helpers

    private function backupPayload(BackupService $backups, OwnerSetting $settings): array
    {
        $rows = Backup::with('creator')->orderByDesc('id')->limit(15)->get()
            ->map(fn (Backup $b) => $this->backupRow($b, $settings));

        return [
            'last' => $rows->firstWhere('status', 'success') ?? $rows->first(),
            'history' => $rows->values(),
            'schedule' => $backups->schedule(),
        ];
    }

    private function backupRow(Backup $b, OwnerSetting $settings): array
    {
        $when = Carbon::parse($b->created_at)->setTimezone($settings->timezone);

        return [
            'id' => $b->id,
            'status' => $b->status,
            'type' => $b->type,
            'type_label' => ['manual' => 'Manual', 'automatic' => 'Automatic', 'pre-restore' => 'Before restore'][$b->type] ?? ucfirst($b->type),
            'size' => $b->status === 'success' ? $b->size_label : '',
            'when' => $settings->formatDate($when) . ' at ' . $when->format('h:i A'),
            'by' => $b->creator->name ?? 'System',
            'message' => $b->message,
            'available' => $b->status === 'success' && is_file($b->path),
        ];
    }

    private function splitName(?string $name): array
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [''];
        if (count($parts) < 2) {
            return [$parts[0], ''];
        }

        $last = array_pop($parts);

        return [implode(' ', $parts), $last];
    }

    private function databaseLabel(): string
    {
        $driver = DB::connection()->getDriverName();

        try {
            $version = DB::selectOne('select version() as v')->v ?? '';
        } catch (\Throwable) {
            $version = '';
        }

        return trim(ucfirst($driver === 'mysql' ? 'MySQL' : $driver) . ' ' . $version);
    }
}
