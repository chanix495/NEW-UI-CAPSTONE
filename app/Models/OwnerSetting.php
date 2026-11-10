<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-user preferences: notification toggles and language / region.
 */
class OwnerSetting extends Model
{
    protected $table = 'owner_settings';

    protected $fillable = [
        'user_id',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'low_stock_alerts',
        'spoilage_alerts',
        'forecast_alerts',
        'dark_mode',
        'color_theme',
        'font_size',
        'language',
        'date_format',
        'currency',
        'timezone',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'low_stock_alerts' => 'boolean',
        'spoilage_alerts' => 'boolean',
        'forecast_alerts' => 'boolean',
        'dark_mode' => 'boolean',
    ];

    public const LANGUAGES = ['fil' => 'Filipino (Philippines)', 'en' => 'English (United States)'];
    public const DATE_FORMATS = ['m/d/Y' => 'MM/DD/YYYY', 'd/m/Y' => 'DD/MM/YYYY', 'Y-m-d' => 'YYYY-MM-DD'];
    public const CURRENCIES = ['PHP' => '₱ Philippine Peso (PHP)', 'USD' => '$ US Dollar (USD)'];
    public const TIMEZONES = ['Asia/Manila' => 'Asia/Manila (UTC+8)', 'UTC' => 'UTC (GMT+0)'];

    /** Colour themes: the --brand-* shades the whole UI is built on. */
    public const THEMES = [
        'violet' => ['label' => 'Violet', 'swatch' => '#7C3AED', 'shades' => [50 => '#F5F3FF', 100 => '#EDE9FE', 200 => '#DDD6FE', 300 => '#C4B5FD', 400 => '#A78BFA', 500 => '#8B5CF6', 600 => '#7C3AED', 700 => '#6D28D9', 800 => '#5B21B6', 900 => '#4C1D95']],
        'blue' => ['label' => 'Blue', 'swatch' => '#2563EB', 'shades' => [50 => '#EFF6FF', 100 => '#DBEAFE', 200 => '#BFDBFE', 300 => '#93C5FD', 400 => '#60A5FA', 500 => '#3B82F6', 600 => '#2563EB', 700 => '#1D4ED8', 800 => '#1E40AF', 900 => '#1E3A8A']],
        'green' => ['label' => 'Green', 'swatch' => '#16A34A', 'shades' => [50 => '#F0FDF4', 100 => '#DCFCE7', 200 => '#BBF7D0', 300 => '#86EFAC', 400 => '#4ADE80', 500 => '#22C55E', 600 => '#16A34A', 700 => '#15803D', 800 => '#166534', 900 => '#14532D']],
        'orange' => ['label' => 'Orange', 'swatch' => '#F97316', 'shades' => [50 => '#FFF7ED', 100 => '#FFEDD5', 200 => '#FED7AA', 300 => '#FDBA74', 400 => '#FB923C', 500 => '#F97316', 600 => '#EA580C', 700 => '#C2410C', 800 => '#9A3412', 900 => '#7C2D12']],
        'teal' => ['label' => 'Teal', 'swatch' => '#0D9488', 'shades' => [50 => '#F0FDFA', 100 => '#CCFBF1', 200 => '#99F6E4', 300 => '#5EEAD4', 400 => '#2DD4BF', 500 => '#14B8A6', 600 => '#0D9488', 700 => '#0F766E', 800 => '#115E59', 900 => '#134E4A']],
    ];

    /** Font sizes: label => page zoom. */
    public const FONT_SIZES = ['small' => 0.92, 'medium' => 1.0, 'large' => 1.1];

    /** What the layout needs to paint the user's appearance. */
    public function appearance(): array
    {
        $theme = array_key_exists($this->color_theme, self::THEMES) ? $this->color_theme : 'violet';
        $size = array_key_exists($this->font_size, self::FONT_SIZES) ? $this->font_size : 'medium';

        return [
            'dark' => (bool) $this->dark_mode,
            'theme' => $theme,
            'font' => $size,
            'shades' => self::THEMES[$theme]['shades'],
            'zoom' => self::FONT_SIZES[$size],
        ];
    }

    /** The user's settings row, created with defaults on first use. */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId])->refresh();
    }

    /** Long date in the user's chosen format, e.g. "November 10, 2026" / "10 November 2026" / "2026-11-10". */
    public function formatDate($date, bool $long = true): string
    {
        $date = \Carbon\Carbon::parse($date)->setTimezone($this->timezone);

        if (!$long) {
            return $date->format($this->date_format);
        }

        return match ($this->date_format) {
            'd/m/Y' => $date->format('j F Y'),
            'Y-m-d' => $date->format('Y-m-d'),
            default => $date->format('F j, Y'),
        };
    }
}
