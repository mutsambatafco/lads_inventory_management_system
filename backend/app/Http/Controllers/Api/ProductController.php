<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\MongoDB\ActivityLog;
use App\Models\MongoDB\SystemLog;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($request->filled('category_id')) {
            $query->byCategory($request->integer('category_id'));
        }
        if ($request->filled('supplier_name')) {
            $query->bySupplier($request->string('supplier_name'));
        }
        if ($request->boolean('only_active')) {
            $query->active();
        }

        $products = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Product::$validationRules);
        $product = Product::create($validated);
        ActivityLog::logActivity([
            'user_id' => optional($request->user())->id,
            'user_name' => optional($request->user())->name,
            'action' => 'create',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'model_name' => $product->name,
            'changes' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'description' => 'Product created',
        ]);
        SystemLog::log('info', 'Product created', ['product_id' => $product->id]);
        return response()->json($product->fresh('category'), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate(Product::updateValidationRules($product->id));
        $before = $product->getOriginal();
        $product->update($validated);
        ActivityLog::logActivity([
            'user_id' => optional($request->user())->id,
            'user_name' => optional($request->user())->name,
            'action' => 'update',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'model_name' => $product->name,
            'changes' => [
                'before' => $before,
                'after' => $product->toArray(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'description' => 'Product updated',
        ]);
        SystemLog::log('info', 'Product updated', ['product_id' => $product->id]);
        return response()->json($product->fresh('category'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        ActivityLog::logActivity([
            'user_id' => optional(request()->user())->id,
            'user_name' => optional(request()->user())->name,
            'action' => 'delete',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'model_name' => $product->name,
            'changes' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'description' => 'Product deleted',
        ]);
        SystemLog::log('warning', 'Product deleted', ['product_id' => $product->id]);
        return response()->json(['message' => 'Deleted']);
    }
}

