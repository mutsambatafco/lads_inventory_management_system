<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'sku', 'barcode', 'unit_price', 'cost_price',
        'current_quantity', 'min_stock_level', 'max_stock_level', 'category_id',
        'supplier_name', 'storage_location', 'status', 'specifications'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'current_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'specifications' => 'array',
    ];

    protected $appends = [
        'stock_status',
    ];

    // Validation rules for creating/updating products
    public static $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'sku' => 'required|string|unique:products,sku',
        'barcode' => 'nullable|string|unique:products,barcode',
        'unit_price' => 'required|numeric|min:0',
        'cost_price' => 'nullable|numeric|min:0',
        'current_quantity' => 'required|integer|min:0',
        'min_stock_level' => 'required|integer|min:0',
        'max_stock_level' => 'nullable|integer|min:0',
        'category_id' => 'required|exists:categories,id',
        'supplier_name' => 'required|string|max:255',
        'storage_location' => 'nullable|string|max:255',
        'status' => 'sometimes|in:active,inactive,discontinued',
        'specifications' => 'nullable|array'
    ];

    public static function updateValidationRules($productId)
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'sometimes|string|unique:products,sku,' . $productId,
            'barcode' => 'nullable|string|unique:products,barcode,' . $productId,
            'unit_price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'current_quantity' => 'sometimes|integer|min:0',
            'min_stock_level' => 'sometimes|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'supplier_name' => 'sometimes|string|max:255',
            'storage_location' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,inactive,discontinued',
            'specifications' => 'nullable|array'
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    // Helper methods
    public function isLowStock(): bool
    {
        return $this->current_quantity <= $this->min_stock_level;
    }

    public function isOverstocked(): bool
    {
        return $this->max_stock_level && $this->current_quantity > $this->max_stock_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_quantity === 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }
        if ($this->isLowStock()) {
            return 'low_stock';
        }
        if ($this->isOverstocked()) {
            return 'overstocked';
        }
        return 'in_stock';
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_quantity <= min_stock_level')
                    ->where('status', 'active');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_quantity', 0)
                    ->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySupplier($query, $supplierName)
    {
        return $query->where('supplier_name', $supplierName);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('storage_location', 'like', "%{$location}%");
    }
}