<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MongoDB\ActivityLog;
use App\Models\MongoDB\SystemLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->withProductCount()->orderBy('name')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Category::$validationRules);
        $category = Category::create($validated);
        ActivityLog::logActivity([
            'user_id' => optional($request->user())->id,
            'user_name' => optional($request->user())->name,
            'action' => 'create',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'model_name' => $category->name,
            'changes' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'description' => 'Category created',
        ]);
        SystemLog::log('info', 'Category created', ['category_id' => $category->id]);
        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->loadCount('products'));
    }

    public function update(Request $request, Category $category)
    {
        $rules = Category::$validationRules;
        // Adjust unique rule to ignore current category
        $rules['name'] = 'required|string|max:255|unique:categories,name,' . $category->id;
        $validated = $request->validate($rules);
        $before = $category->getOriginal();
        $category->update($validated);
        ActivityLog::logActivity([
            'user_id' => optional($request->user())->id,
            'user_name' => optional($request->user())->name,
            'action' => 'update',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'model_name' => $category->name,
            'changes' => ['before' => $before, 'after' => $category->toArray()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'description' => 'Category updated',
        ]);
        SystemLog::log('info', 'Category updated', ['category_id' => $category->id]);
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        ActivityLog::logActivity([
            'user_id' => optional(request()->user())->id,
            'user_name' => optional(request()->user())->name,
            'action' => 'delete',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'model_name' => $category->name,
            'changes' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'description' => 'Category deleted',
        ]);
        SystemLog::log('warning', 'Category deleted', ['category_id' => $category->id]);
        return response()->json(['message' => 'Deleted']);
    }
}

