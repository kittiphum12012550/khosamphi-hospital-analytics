<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\VisitStatistic;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisitStatisticSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin?->id ?? 1;

        $opd = Department::where('code', 'OPD')->first();
        $er = Department::where('code', 'ER')->first();
        $ipd = Department::where('code', 'IPD')->first();
        $dent = Department::where('code', 'DENT')->first();
        $pt = Department::where('code', 'PT')->first();
        $ttm = Department::where('code', 'TTM')->first();
        $lab = Department::where('code', 'LAB')->first();
        $pharm = Department::where('code', 'PHARM')->first();
        $ncd = Department::where('code', 'NCD')->first();

        $genExam = ServiceType::where('code', 'GEN_EXAM')->first();
        $emergency = ServiceType::where('code', 'EMERGENCY')->first();
        $ncdCare = ServiceType::where('code', 'NCD_CARE')->first();
        $screening = ServiceType::where('code', 'HEALTH_SCREEN')->first();
        $vaccine = ServiceType::where('code', 'VACCINE')->first();
        $dental = ServiceType::where('code', 'DENTAL_CARE')->first();
        $rehab = ServiceType::where('code', 'REHAB')->first();
        $thaiMed = ServiceType::where('code', 'THAI_MED')->first();
        $labTest = ServiceType::where('code', 'LAB_TEST')->first();
        $dispense = ServiceType::where('code', 'DISPENSE')->first();

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(60); // 60 days of synthetic data

        $records = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($today)) {
            $dateStr = $currentDate->format('Y-m-d');
            $isWeekend = $currentDate->isWeekend();

            // 1. OPD - Morning Peak (08:00 - 11:00)
            if (!$isWeekend && $opd && $genExam) {
                $hours = [8 => [25, 45], 9 => [35, 60], 10 => [30, 50], 11 => [15, 30], 13 => [10, 25], 14 => [10, 20]];
                foreach ($hours as $h => $range) {
                    $records[] = [
                        'department_id' => $opd->id,
                        'service_type_id' => $genExam->id,
                        'service_date' => $dateStr,
                        'service_hour' => $h,
                        'visit_count' => rand($range[0], $range[1]),
                        'source_type' => 'manual',
                        'batch_id' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $currentDate->copy()->setHour($h),
                        'updated_at' => $currentDate->copy()->setHour($h),
                    ];
                }
            }

            // 2. ER - Emergency Room (24 hours, active everyday)
            if ($er && $emergency) {
                $erHours = [1, 5, 8, 11, 14, 17, 19, 20, 21, 23];
                foreach ($erHours as $h) {
                    $count = ($h >= 17 && $h <= 22) ? rand(5, 16) : rand(1, 8);
                    $records[] = [
                        'department_id' => $er->id,
                        'service_type_id' => $emergency->id,
                        'service_date' => $dateStr,
                        'service_hour' => $h,
                        'visit_count' => $count,
                        'source_type' => 'manual',
                        'batch_id' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $currentDate->copy()->setHour($h),
                        'updated_at' => $currentDate->copy()->setHour($h),
                    ];
                }
            }

            // 3. NCD Clinic (Mon, Wed, Fri morning)
            if (in_array($currentDate->dayOfWeek, [1, 3, 5]) && $ncd && $ncdCare) {
                foreach ([8, 9, 10] as $h) {
                    $records[] = [
                        'department_id' => $ncd->id,
                        'service_type_id' => $ncdCare->id,
                        'service_date' => $dateStr,
                        'service_hour' => $h,
                        'visit_count' => rand(20, 45),
                        'source_type' => 'manual',
                        'batch_id' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $currentDate->copy()->setHour($h),
                        'updated_at' => $currentDate->copy()->setHour($h),
                    ];
                }
            }

            // 4. Dental (Weekdays)
            if (!$isWeekend && $dent && $dental) {
                foreach ([9, 10, 13, 14] as $h) {
                    $records[] = [
                        'department_id' => $dent->id,
                        'service_type_id' => $dental->id,
                        'service_date' => $dateStr,
                        'service_hour' => $h,
                        'visit_count' => rand(4, 12),
                        'source_type' => 'manual',
                        'batch_id' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $currentDate->copy()->setHour($h),
                        'updated_at' => $currentDate->copy()->setHour($h),
                    ];
                }
            }

            // 5. Physical Therapy & Thai Medicine (Weekdays)
            if (!$isWeekend) {
                if ($pt && $rehab) {
                    foreach ([9, 10, 13, 14] as $h) {
                        $records[] = [
                            'department_id' => $pt->id,
                            'service_type_id' => $rehab->id,
                            'service_date' => $dateStr,
                            'service_hour' => $h,
                            'visit_count' => rand(3, 9),
                            'source_type' => 'manual',
                            'batch_id' => null,
                            'created_by' => $adminId,
                            'updated_by' => $adminId,
                            'created_at' => $currentDate->copy()->setHour($h),
                            'updated_at' => $currentDate->copy()->setHour($h),
                        ];
                    }
                }

                if ($ttm && $thaiMed) {
                    foreach ([9, 11, 13, 15] as $h) {
                        $records[] = [
                            'department_id' => $ttm->id,
                            'service_type_id' => $thaiMed->id,
                            'service_date' => $dateStr,
                            'service_hour' => $h,
                            'visit_count' => rand(4, 10),
                            'source_type' => 'manual',
                            'batch_id' => null,
                            'created_by' => $adminId,
                            'updated_by' => $adminId,
                            'created_at' => $currentDate->copy()->setHour($h),
                            'updated_at' => $currentDate->copy()->setHour($h),
                        ];
                    }
                }
            }

            // 6. Clinical Lab (Early morning peak)
            if (!$isWeekend && $lab && $labTest) {
                foreach ([7, 8, 9, 10] as $h) {
                    $records[] = [
                        'department_id' => $lab->id,
                        'service_type_id' => $labTest->id,
                        'service_date' => $dateStr,
                        'service_hour' => $h,
                        'visit_count' => rand(25, 65),
                        'source_type' => 'manual',
                        'batch_id' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $currentDate->copy()->setHour($h),
                        'updated_at' => $currentDate->copy()->setHour($h),
                    ];
                }
            }

            // 7. Pharmacy (Dispensing correlate with OPD/NCD)
            if (!$isWeekend && $pharm && $dispense) {
                foreach ([9, 10, 11, 14, 15] as $h) {
                    $records[] = [
                        'department_id' => $pharm->id,
                        'service_type_id' => $dispense->id,
                        'service_date' => $dateStr,
                        'service_hour' => $h,
                        'visit_count' => rand(30, 70),
                        'source_type' => 'manual',
                        'batch_id' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                        'created_at' => $currentDate->copy()->setHour($h),
                        'updated_at' => $currentDate->copy()->setHour($h),
                    ];
                }
            }

            $currentDate->addDay();
        }

        // Insert in batches of 500 to keep it blazing fast
        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('visit_statistics')->upsert(
                $chunk,
                ['service_date', 'department_id', 'service_type_id', 'service_hour'],
                ['visit_count', 'updated_at']
            );
        }
    }
}

