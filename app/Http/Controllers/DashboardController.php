<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ServiceType;
use App\Models\VisitStatistic;
use App\Services\DashboardAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardAnalyticsService $analyticsService;

    public function __construct(DashboardAnalyticsService $analyticsService)
    {
        $this->middleware('auth');
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'department_id' => $request->input('department_id'),
            'service_type_id' => $request->input('service_type_id'),
            'year' => $request->input('year', Carbon::now()->year),
            'month' => $request->input('month'),
        ];

        // Active master data for dropdown filters
        $departments = Department::active()->get();
        $serviceTypes = ServiceType::active()->get();

        // Distinct years in database for year filter dropdown
        $availableYears = $this->analyticsService->getAvailableYears();

        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        }

        // Summary KPI Metrics
        $summary = $this->analyticsService->getSummaryCards($filters);

        // Chart Data Packages
        $dailyTrend = $this->analyticsService->getDailyTrend($filters);
        $departmentBreakdown = $this->analyticsService->getDepartmentBreakdown($filters);
        $usersByDepartment = $this->analyticsService->getUsersByDepartment();
        $serviceDistribution = $this->analyticsService->getServiceTypeDistribution($filters);
        $monthlyComparison = $this->analyticsService->getMonthlyComparison($filters);
        $hourlyDistribution = $this->analyticsService->getHourlyDistribution($filters);

        // Recent visit logs for quick glance
        $recentVisits = VisitStatistic::with(['department', 'serviceType', 'creator'])
            ->filter($filters)
            ->latest('service_date')
            ->latest('service_hour')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'filters',
            'departments',
            'serviceTypes',
            'availableYears',
            'summary',
            'dailyTrend',
            'departmentBreakdown',
            'usersByDepartment',
            'serviceDistribution',
            'monthlyComparison',
            'hourlyDistribution',
            'recentVisits'
        ));
    }

    /**
     * AJAX endpoint to return JSON chart and KPI data for dynamic filter adjustments
     */
    public function getAnalyticsData(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'department_id' => $request->input('department_id'),
            'service_type_id' => $request->input('service_type_id'),
            'year' => $request->input('year', Carbon::now()->year),
            'month' => $request->input('month'),
        ];

        return response()->json([
            'summary' => $this->analyticsService->getSummaryCards($filters),
            'daily_trend' => $this->analyticsService->getDailyTrend($filters),
            'department_breakdown' => $this->analyticsService->getDepartmentBreakdown($filters),
            'users_by_department' => $this->analyticsService->getUsersByDepartment(),
            'service_distribution' => $this->analyticsService->getServiceTypeDistribution($filters),
            'monthly_comparison' => $this->analyticsService->getMonthlyComparison($filters),
            'hourly_distribution' => $this->analyticsService->getHourlyDistribution($filters),
        ]);
    }
}
