<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RentalPeriod;
use App\Models\Rolador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get weekly statistics including total generated and number of paying roladores,
     * along with trend comparison to previous week
     */
    public function weeklyStats(Request $request)
    {
        $request->validate([
            'week' => 'required|integer|min:1|max:53',
            'year' => 'required|integer|min:2000'
        ]);

        $week = $request->input('week');
        $year = $request->input('year');

        // Get start and end dates for the requested week
        $startDate = Date::now()->setISODate($year, $week)->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        // Get start and end dates for the previous week
        $prevStartDate = $startDate->copy()->subWeek();
        $prevEndDate = $prevStartDate->copy()->endOfWeek();

        // Get current week stats
        $currentWeekStats = $this->getWeekStats($startDate, $endDate);

        // Get previous week stats for trend
        $previousWeekStats = $this->getWeekStats($prevStartDate, $prevEndDate);

        // Calculate trends
        $totalTrend = $this->calculateTrend($currentWeekStats['income_stats']['total_generated'], $previousWeekStats['income_stats']['total_generated']);
        $payingRoladorsTrend = $this->calculateTrend($currentWeekStats['payment_stats']['paying_roladores'], $previousWeekStats['payment_stats']['paying_roladores']);

        return response()->json([
            'total_generated' => [
                'value' => $currentWeekStats['income_stats']['total_generated'],
                'trend' => $totalTrend
            ],
            'paying_roladores' => [
                'value' => $currentWeekStats['payment_stats']['paying_roladores'],
                'total' => $currentWeekStats['payment_stats']['total_roladores'],
                'rate' => $currentWeekStats['payment_stats']['total_roladores']
                    ? $currentWeekStats['payment_stats']['paying_roladores'] / $currentWeekStats['payment_stats']['total_roladores']
                    : 0,
                'trend' => $payingRoladorsTrend
            ]
        ]);
    }

    /**
     * Get statistics for roladores by category, showing only the most significant categories
     * and grouping the rest under "Others"
     */
    public function categoryDistribution()
    {
        $limit = 5;
        $count = Category::count();

        // Get all categories with their rolador counts
        $topCategories = Category::select('categories.id', 'categories.name')
            ->selectRaw('COUNT(roladors.id) as rolador_count')
            ->leftJoin('roladors', 'categories.id', '=', 'roladors.category_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('rolador_count', 'desc')
            ->limit($count == $limit ? $limit : $limit - 1)
            ->get();


        // If we have more than 12 categories, group the smaller ones into "Others"
        if ($count > $limit) {
            logger('', $topCategories->pluck('id')->toArray());
            $otherCategories = Rolador::whereNotIn('category_id', $topCategories->pluck('id')->toArray())->count();

            $topCategories->push([
                'name' => 'Otras',
                'rolador_count' => $otherCategories
            ]);
        }

        return response()->json($topCategories);
    }

    /**
     * Helper function to get stats for a specific week period
     */
    private function getWeekStats(Carbon $startDate, Carbon $endDate)
    {
        $incomeStats = RentalPeriod::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount_due');

        $paymentStats = RentalPeriod::whereBetween('end_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT rolador_id) as total_roladores,
                SUM(IF(payment_date IS NOT NULL AND payment_date <= end_date, 1, 0)) as paying_roladores
            ')
            ->first();

        return [
            'income_stats' => [
                'total_generated' => (float) $incomeStats,
            ],
            'payment_stats' => [
                'total_roladores' => (int) ($paymentStats->total_roladores ?? 0),
                'paying_roladores' => (int) ($paymentStats->paying_roladores ?? 0),
            ]
        ];
    }

    /**
     * Helper function to calculate percentage trend between current and previous values
     */
    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
