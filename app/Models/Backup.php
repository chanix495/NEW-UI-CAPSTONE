<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = ['filename', 'size_bytes', 'type', 'status', 'message', 'created_by'];

    protected $casts = ['size_bytes' => 'integer'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPathAttribute(): string
    {
        return storage_path('app/backups/' . $this->filename);
    }

    public function getSizeLabelAttribute(): string
    {
        $b = $this->size_bytes;
        if ($b >= 1048576) {
            return round($b / 1048576, 1) . ' MB';
        }

        return max(round($b / 1024, 1), 0.1) . ' KB';
    }
}
