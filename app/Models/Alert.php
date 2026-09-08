<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    public $timestamps = false;

    protected $fillable = ['icon', 'title', 'detail', 'severity', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public static function log(string $icon, string $title, string $detail, string $severity = 'info'): self
    {
        return self::create([
            'icon' => $icon,
            'title' => $title,
            'detail' => $detail,
            'severity' => $severity,
            'created_at' => now(),
        ]);
    }
}
