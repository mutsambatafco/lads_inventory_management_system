<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\MongoDB\InventoryAudit;
use App\Models\MongoDB\SystemLog;
use App\Models\MongoDB\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $userId = $request->user()->id;

        $adjustment = DB::transaction(function () use ($validated, $userId) {
            /** @var Product $product */
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $oldQuantity = $product->current_quantity;
            $newQuantity = $validated['new_quantity'];
            $difference = $newQuantity - $oldQuantity;

            $product->update(['current_quantity' => $newQuantity]);

            $adj = InventoryAdjustment::create([
                'product_id' => $product->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment' => $difference,
                'reason' => $validated['reason'],
                'user_id' => $userId,
                'notes' => $validated['notes'] ?? null,
            ]);
            // Log to Mongo collections
            InventoryAudit::logAudit([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'action' => 'inventory_adjustment',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'change' => $difference,
                'reason' => $validated['reason'],
                'user_id' => $userId,
                'user_name' => optional(request()->user())->name,
                'source' => 'manual',
                'reference_id' => $adj->id,
                'metadata' => [
                    'product_sku' => $product->sku,
                ],
            ]);

            ActivityLog::logActivity([
                'user_id' => $userId,
                'user_name' => optional(request()->user())->name,
                'action' => 'update',
                'model_type' => Product::class,
                'model_id' => $product->id,
                'model_name' => $product->name,
                'changes' => [
                    'current_quantity' => ['from' => $oldQuantity, 'to' => $newQuantity],
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'description' => 'Inventory adjusted',
            ]);

            SystemLog::log('info', 'Inventory adjusted', [
                'product_id' => $product->id,
                'difference' => $difference,
                'reason' => $validated['reason'],
            ]);

            return $adj;
        });

        return response()->json($adjustment, 201);
    }
}

