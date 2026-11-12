<?php

namespace App\Services;

use App\Models\Department;
use App\Models\ServiceType;
use App\Models\VisitStatistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Build aggregated report data based on user options
     */
    public function generateReport(array $filters): array
    {
        $reportType = $filters['report_type'] ?? 'daily'; // daily, monthly, yearly, department, service_type

        $query = VisitStatistic::query()->filter($filters);

        switch ($reportType) {
            case 'monthly':
                $yearExpr = $this->datePartExpression('year');
                $monthExpr = $this->datePartExpression('month');
                $items = $query
                    ->select(
                        DB::raw("{$yearExpr} as year"),
                        DB::raw("{$monthExpr} as month"),
                        DB::raw('SUM(visit_count) as total_visits'),
                        DB::raw('COUNT(DISTINCT service_date) as active_days'),
                        DB::raw('COUNT(DISTINCT department_id) as dept_count')
                    )
                    ->groupBy('year', 'month')
                    ->orderByDesc('year')
                    ->orderByDesc('month')
                    ->get()
                    ->map(function ($row) {
                        $monthNames = [
                            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                        ];
                        $buddhistYear = $row->year + 543;
                        $row->period_label = ($monthNames[$row->month] ?? $row->month) . " พ.ศ. {$buddhistYear}";
                        $row->avg_daily = $row->active_days > 0 ? round($row->total_visits / $row->active_days, 1) : 0;
                        return $row;
                    });
                break;

            case 'yearly':
                $yearExpr = $this->datePartExpression('year');
                $items = $query
                    ->select(
                        DB::raw("{$yearExpr} as year"),
                        DB::raw('SUM(visit_count) as total_visits'),
                        DB::raw('COUNT(DISTINCT service_date) as active_days')
                    )
                    ->groupBy('year')
                    ->orderByDesc('year')
                    ->get()
                    ->map(function ($row) {
                        $buddhistYear = $row->year + 543;
                        $row->period_label = "ปี พ.ศ. {$buddhistYear} (ค.ศ. {$row->year})";
                        $row->avg_daily = $row->active_days > 0 ? round($row->total_visits / $row->active_days, 1) : 0;
                        return $row;
                    });
                break;

            case 'department':
                $items = $query
                    ->join('departments', 'visit_statistics.department_id', '=', 'departments.id')
                    ->select(
                        'departments.id as dept_id',
                        'departments.code as dept_code',
                        'departments.name_th as dept_name',
                        DB::raw('SUM(visit_statistics.visit_count) as total_visits'),
                        DB::raw('COUNT(DISTINCT visit_statistics.service_date) as active_days')
                    )
                    ->groupBy('departments.id', 'departments.code', 'departments.name_th')
                    ->orderByDesc('total_visits')
                    ->get();
                break;

            case 'service_type':
                $items = $query
                    ->join('service_types', 'visit_statistics.service_type_id', '=', 'service_types.id')
                    ->select(
                        'service_types.id as st_id',
                        'service_types.code as st_code',
                        'service_types.name as st_name',
                        'service_types.category as st_category',
                        DB::raw('SUM(visit_statistics.visit_count) as total_visits')
                    )
                    ->groupBy('service_types.id', 'service_types.code', 'service_types.name', 'service_types.category')
                    ->orderByDesc('total_visits')
                    ->get();
                break;

            case 'daily':
            default:
                $items = $query
                    ->select(
                        'service_date',
                        DB::raw('SUM(visit_count) as total_visits'),
                        DB::raw('COUNT(DISTINCT department_id) as dept_count'),
                        DB::raw('COUNT(DISTINCT service_type_id) as service_count')
                    )
                    ->groupBy('service_date')
                    ->orderByDesc('service_date')
                    ->limit(100)
                    ->get()
                    ->map(function ($row) {
                        $c = Carbon::parse($row->service_date)->locale('th');
                        $row->period_label = $c->isoFormat('D MMMM') . ' พ.ศ. ' . ($c->year + 543);
                        return $row;
                    });
                break;
        }

        $grandTotal = $items->sum('total_visits');

        return [
            'report_type' => $reportType,
            'filters' => $filters,
            'items' => $items,
            'grand_total' => $grandTotal,
            'generated_at' => Carbon::now()->locale('th')->isoFormat('D MMMM YYYY H:mm น.'),
            'hospital_name' => config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร'),
        ];
    }

    private function datePartExpression(string $part): string
    {
        $driver = DB::connection()->getDriverName();

        return match ([$driver, $part]) {
            ['sqlite', 'year'] => "strftime('%Y', service_date)",
            ['sqlite', 'month'] => "CAST(strftime('%m', service_date) AS INTEGER)",
            ['pgsql', 'year'] => 'EXTRACT(YEAR FROM service_date)',
            ['pgsql', 'month'] => 'EXTRACT(MONTH FROM service_date)',
            ['mysql', 'year'], ['mariadb', 'year'] => 'YEAR(service_date)',
            ['mysql', 'month'], ['mariadb', 'month'] => 'MONTH(service_date)',
            default => 'YEAR(service_date)',
        };
    }
}

