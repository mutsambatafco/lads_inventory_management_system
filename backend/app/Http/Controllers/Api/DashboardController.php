<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\MongoDB\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function summary()
    {
        try {
            $summary = Cache::tags(['dashboard', 'products', 'categories'])->remember('dashboard:summary', now()->addMinutes(2), function () {
                return $this->buildSummary();
            });
        } catch (\Throwable $e) {
            $summary = $this->buildSummary();
        }

        return response()->json($summary);
    }

    private function buildSummary(): array
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

        return [
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'lowStockItems' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,
            'categories' => $categories,
            'recentActivity' => $recentActivity,
        ];
    }
}

