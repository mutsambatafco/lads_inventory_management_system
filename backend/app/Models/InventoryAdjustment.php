<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'old_quantity', 'new_quantity',
        'adjustment', 'reason', 'user_id', 'user_name', 'notes'
    ];

    protected $casts = [
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'adjustment' => 'integer',
    ];

    public static $validationRules = [
        'product_id' => 'required|exists:products,id',
        'adjustment' => 'required|integer',
        'reason' => 'required|string|max:255',
        'notes' => 'nullable|string',
        'user_id' => 'required|exists:users,id',
        'user_name' => 'required|string|max:255'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeStockIn($query)
    {
        return $query->where('adjustment', '>', 0);
    }

    public function scopeStockOut($query)
    {
        return $query->where('adjustment', '<', 0);
    }

    public function getTypeAttribute(): string
    {
        return $this->adjustment > 0 ? 'stock_in' : 'stock_out';
    }

    public function getAbsoluteAdjustmentAttribute(): int
    {
        return abs($this->adjustment);
    }
}