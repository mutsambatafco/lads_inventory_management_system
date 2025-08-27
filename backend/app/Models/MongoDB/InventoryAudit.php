<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;

class InventoryAudit extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'inventory_audits';

    protected $fillable = [
        'product_id', 'product_name', 'action', 'old_quantity',
        'new_quantity', 'change', 'reason', 'user_id', 'user_name',
        'source', 'reference_id', 'metadata'
    ];

    protected $casts = [
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'change' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public static function logAudit($data)
    {
        return static::create($data);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeStockIn($query)
    {
        return $query->where('change', '>', 0);
    }

    public function scopeStockOut($query)
    {
        return $query->where('change', '<', 0);
    }

    public function getTypeAttribute(): string
    {
        return $this->change > 0 ? 'stock_in' : 'stock_out';
    }

    public function getAbsoluteChangeAttribute(): int
    {
        return abs($this->change);
    }
}