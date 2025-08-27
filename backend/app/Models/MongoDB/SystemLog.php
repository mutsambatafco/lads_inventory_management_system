<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;

class SystemLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'system_logs';

    protected $fillable = [
        'level', 'message', 'context', 'channel',
        'extra', 'user_id', 'user_name', 'ip_address',
        'url', 'method'
    ];

    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public static function log($level, $message, array $context = [])
    {
        return static::create([
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'channel' => config('logging.default', 'laravel'),
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method()
        ]);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeError($query)
    {
        return $query->where('level', 'error');
    }

    public function scopeWarning($query)
    {
        return $query->where('level', 'warning');
    }

    public function scopeInfo($query)
    {
        return $query->where('level', 'info');
    }
}