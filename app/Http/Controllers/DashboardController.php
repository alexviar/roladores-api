<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CreditPayment;
use App\Models\RentalPeriod;
use App\Models\Rolador;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    /**
     * Get weekly statistics including total generated and number of paying roladores,
     * along with trend comparison to previous week
     */
    public function dailyStats(Request $request)
    {
        Gate::allowIf(fn(User $user) => $user->email === 'admin@plazadelvestido.com');

        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->input('date');

        $startDate = Date::parse($date)->startOfDay();
        $endDate = Date::parse($date)->endOfDay();

        $prevStartDate = $startDate->copy()->subDay();
        $prevEndDate = $prevStartDate->copy()->endOfDay();

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
        Gate::allowIf(fn(User $user) => $user->email === 'admin@plazadelvestido.com');

        return Category::withCount(['roladores'])->paginate();
    }

    /**
     * Devuelve el log de actividad reciente (últimas 50 acciones)
     */
    public function activityLog(Request $request)
    {
        Gate::allowIf(fn(User $user) => $user->email === 'admin@plazadelvestido.com');

        $query = Activity::with('causer')->latest();

        $request->whenFilled('filter.subject', function ($subject) use ($query) {
            if ($subject === 'payments') {
                $query->whereIn('subject_type', [CreditPayment::class, RentalPeriod::class]);
            } else if ($subject === 'roladores') {
                $query->where('subject_type', Rolador::class);
            }
        });

        $request->whenFilled('filter.date', function ($date) use ($query) {
            $query->whereDate('created_at', $date);
        });

        $activities = $query
            ->paginate();

        return $activities;
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
                COUNT(DISTINCT rolador_id) as paying_roladores
            ')
            ->first();

        return [
            'income_stats' => [
                'total_generated' => (float) $incomeStats,
            ],
            'payment_stats' => [
                'total_roladores' => (int) Rolador::count(),
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
