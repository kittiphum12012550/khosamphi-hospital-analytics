<?php

namespace App\Services;

use App\Models\Department;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\VisitStatistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Get KPI Summary Cards data based on filters
     */
    public function getSummaryCards(array $filters): array
    {
        $baseQuery = VisitStatistic::query()->filter($filters);

        // Filtered Total Visits
        $totalVisits = (int) (clone $baseQuery)->sum('visit_count');

        // Today Visits (ignoring date range filter to always show real-time today metric, but respecting dept/service filter)
        $todayQuery = VisitStatistic::query()
            ->whereDate('service_date', Carbon::today());
        if (!empty($filters['department_id'])) {
            $todayQuery->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['service_type_id'])) {
            $todayQuery->where('service_type_id', $filters['service_type_id']);
        }
        $todayVisits = (int) $todayQuery->sum('visit_count');

        // This Month Visits
        $thisMonthQuery = VisitStatistic::query()
            ->whereYear('service_date', Carbon::now()->year)
            ->whereMonth('service_date', Carbon::now()->month);
        if (!empty($filters['department_id'])) {
            $thisMonthQuery->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['service_type_id'])) {
            $thisMonthQuery->where('service_type_id', $filters['service_type_id']);
        }
        $thisMonthVisits = (int) $thisMonthQuery->sum('visit_count');

        // Top Department by Visits in the filtered period
        $topDepartment = (clone $baseQuery)
            ->join('departments', 'visit_statistics.department_id', '=', 'departments.id')
            ->select('departments.name_th', 'departments.code', DB::raw('SUM(visit_statistics.visit_count) as total'))
            ->groupBy('departments.id', 'departments.name_th', 'departments.code')
            ->orderByDesc('total')
            ->first();

        // Active Departments Count
        $activeDepartmentsCount = Department::where('is_active', true)->count();

        // Average Visits per day in filtered period
        $distinctDays = (clone $baseQuery)->distinct()->count('service_date');
        $avgDailyVisits = $distinctDays > 0 ? round($totalVisits / $distinctDays, 1) : 0;

        return [
            'total_visits' => $totalVisits,
            'today_visits' => $todayVisits,
            'this_month_visits' => $thisMonthVisits,
            'top_department_name' => $topDepartment ? "{$topDepartment->code} - {$topDepartment->name_th}" : 'ไม่มีข้อมูล',
            'top_department_count' => $topDepartment ? (int) $topDepartment->total : 0,
            'active_departments' => $activeDepartmentsCount,
            'avg_daily_visits' => $avgDailyVisits,
        ];
    }

    /**
     * Get Daily Trend Line data
     */
    public function getDailyTrend(array $filters): array
    {
        $query = VisitStatistic::query()->filter($filters);

        // If no date filters, default to past 30 days
        if (empty($filters['start_date']) && empty($filters['year']) && empty($filters['month'])) {
            $query->whereDate('service_date', '>=', Carbon::now()->subDays(30));
        }

        $results = $query
            ->select('service_date', DB::raw('SUM(visit_count) as total'))
            ->groupBy('service_date')
            ->orderBy('service_date', 'asc')
            ->get();

        $labels = [];
        $data = [];

        foreach ($results as $row) {
            $labels[] = Carbon::parse($row->service_date)->locale('th')->isoFormat('D MMM YYYY');
            $data[] = (int) $row->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get Department Comparison Bar Chart data
     */
    public function getDepartmentBreakdown(array $filters): array
    {
        $results = VisitStatistic::query()->filter($filters)
            ->join('departments', 'visit_statistics.department_id', '=', 'departments.id')
            ->select('departments.name_th', 'departments.code', DB::raw('SUM(visit_statistics.visit_count) as total'))
            ->groupBy('departments.id', 'departments.name_th', 'departments.code')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $data = [];

        foreach ($results as $row) {
            $labels[] = "{$row->code} - {$row->name_th}";
            $data[] = (int) $row->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get user account counts grouped by their assigned department
     */
    public function getUsersByDepartment(): array
    {
        $results = User::query()
            ->select('department_name', DB::raw('COUNT(*) as total'))
            ->groupBy('department_name')
            ->orderByDesc('total')
            ->orderBy('department_name')
            ->get();

        $departmentCounts = [];

        foreach ($results as $row) {
            $department = trim((string) $row->department_name);
            $label = $department !== '' ? $department : 'ไม่ระบุแผนก';
            $departmentCounts[$label] = ($departmentCounts[$label] ?? 0) + (int) $row->total;
        }

        arsort($departmentCounts, SORT_NUMERIC);

        return [
            'labels' => array_keys($departmentCounts),
            'data' => array_values($departmentCounts),
        ];
    }

    /**
     * Get Service Type Distribution Pie/Doughnut Chart data
     */
    public function getServiceTypeDistribution(array $filters): array
    {
        $results = VisitStatistic::query()->filter($filters)
            ->join('service_types', 'visit_statistics.service_type_id', '=', 'service_types.id')
            ->select('service_types.name', 'service_types.code', DB::raw('SUM(visit_statistics.visit_count) as total'))
            ->groupBy('service_types.id', 'service_types.name', 'service_types.code')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $data = [];

        foreach ($results as $row) {
            $labels[] = $row->name;
            $data[] = (int) $row->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get Monthly Comparison Chart data for a year
     */
    public function getMonthlyComparison(array $filters): array
    {
        $selectedYear = !empty($filters['year']) ? (int) $filters['year'] : Carbon::now()->year;
        $previousYear = $selectedYear - 1;

        $monthNames = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        // Fetch selected year
        $currentQuery = VisitStatistic::query()
            ->whereYear('service_date', $selectedYear);
        if (!empty($filters['department_id'])) {
            $currentQuery->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['service_type_id'])) {
            $currentQuery->where('service_type_id', $filters['service_type_id']);
        }
        $currentResults = $currentQuery
            ->select(DB::raw('MONTH(service_date) as month_num'), DB::raw('SUM(visit_count) as total'))
            ->groupBy('month_num')
            ->pluck('total', 'month_num')
            ->toArray();

        // Fetch previous year for comparison
        $prevQuery = VisitStatistic::query()
            ->whereYear('service_date', $previousYear);
        if (!empty($filters['department_id'])) {
            $prevQuery->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['service_type_id'])) {
            $prevQuery->where('service_type_id', $filters['service_type_id']);
        }
        $prevResults = $prevQuery
            ->select(DB::raw('MONTH(service_date) as month_num'), DB::raw('SUM(visit_count) as total'))
            ->groupBy('month_num')
            ->pluck('total', 'month_num')
            ->toArray();

        $labels = [];
        $currentYearData = [];
        $previousYearData = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $monthNames[$m];
            $currentYearData[] = (int) ($currentResults[$m] ?? 0);
            $previousYearData[] = (int) ($prevResults[$m] ?? 0);
        }

        return [
            'labels' => $labels,
            'current_year' => $selectedYear + 543, // Buddhist era
            'current_data' => $currentYearData,
            'previous_year' => $previousYear + 543,
            'previous_data' => $previousYearData,
        ];
    }

    /**
     * Get Hourly Peak Visits distribution (0:00 to 23:00)
     */
    public function getHourlyDistribution(array $filters): array
    {
        $results = VisitStatistic::query()->filter($filters)
            ->select('service_hour', DB::raw('SUM(visit_count) as total'))
            ->groupBy('service_hour')
            ->pluck('total', 'service_hour')
            ->toArray();

        $labels = [];
        $data = [];

        for ($h = 0; $h <= 23; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $data[] = (int) ($results[$h] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
