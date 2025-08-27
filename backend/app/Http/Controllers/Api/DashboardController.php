<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\MongoDB\ActivityLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary()
    {
        $totalItems = Product::count();
        $totalValue = (float) Product::sum(DB::raw('current_quantity * unit_price'));
        $lowStockItems = Product::query()->whereRaw('current_quantity <= min_stock_level')->count();
        $outOfStockItems = Product::query()->where('current_quantity', 0)->count();

        $categories = Category::query()
            ->withCount('products')
            ->get()
            ->map(fn ($c) => ['name' => $c->name, 'count' => $c->products_count])
            ->values();

        $recentActivity = ActivityLog::query()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id' => (string) $a->_id,
                'action' => $a->action,
                'item' => $a->model_name,
                'timestamp' => optional($a->created_at)->toISOString(),
            ])
            ->values();

        return response()->json([
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'lowStockItems' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,
            'categories' => $categories,
            'recentActivity' => $recentActivity,
        ]);
    }
}

