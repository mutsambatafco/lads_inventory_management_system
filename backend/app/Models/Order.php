<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'quantity', 'unit_price', 'total_price',
        'customer_name', 'customer_email', 'customer_phone',
        'order_date', 'delivery_date', 'status',
        'shipping_address', 'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'order_date' => 'date',
        'delivery_date' => 'date',
        'quantity' => 'integer',
    ];

    public static $validationRules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'unit_price' => 'required|numeric|min:0',
        'customer_name' => 'required|string|max:255',
        'customer_email' => 'nullable|email|max:255',
        'customer_phone' => 'nullable|string|max:20',
        'order_date' => 'required|date',
        'delivery_date' => 'nullable|date|after_or_equal:order_date',
        'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
        'shipping_address' => 'nullable|string',
        'notes' => 'nullable|string'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            $order->total_price = $order->quantity * $order->unit_price;
        });

        static::updating(function ($order) {
            $order->total_price = $order->quantity * $order->unit_price;
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['delivered', 'shipped']);
    }
}