<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'quantity', 'unit_cost', 'total_cost',
        'purchase_date', 'invoice_number', 'supplier_name',
        'notes', 'status'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'purchase_date' => 'date',
        'quantity' => 'integer',
    ];

    public static $validationRules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'unit_cost' => 'required|numeric|min:0',
        'purchase_date' => 'required|date',
        'invoice_number' => 'nullable|string|max:255',
        'supplier_name' => 'required|string|max:255',
        'notes' => 'nullable|string',
        'status' => 'sometimes|in:pending,received,cancelled'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::creating(function ($purchase) {
            $purchase->total_cost = $purchase->quantity * $purchase->unit_cost;
        });

        static::updating(function ($purchase) {
            $purchase->total_cost = $purchase->quantity * $purchase->unit_cost;
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('purchase_date', now()->month)
                    ->whereYear('purchase_date', now()->year);
    }
}