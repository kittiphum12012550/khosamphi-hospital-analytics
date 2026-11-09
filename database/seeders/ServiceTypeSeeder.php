<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'GEN_EXAM',
                'name' => 'ตรวจรักษาโรคทั่วไป (General Consultation)',
                'category' => 'รักษาพยาบาล',
                'description' => 'ตรวจวินิจฉัยและรักษาอาการเจ็บป่วยทั่วไป',
                'is_active' => true,
            ],
            [
                'code' => 'EMERGENCY',
                'name' => 'อุบัติเหตุและฉุกเฉิน (Emergency & Trauma)',
                'category' => 'ฉุกเฉิน',
                'description' => 'การช่วยชีวิตและรักษาผู้ป่วยฉุกเฉินวิกฤต/อุบัติเหตุ',
                'is_active' => true,
            ],
            [
                'code' => 'NCD_CARE',
                'name' => 'ดูแลผู้ป่วยโรคเรื้อรัง (NCD Care)',
                'category' => 'ตรวจรักษาต่อเนื่อง',
                'description' => 'ตรวจติดตามเบาหวาน ความดันโลหิต ไขมันในเลือด',
                'is_active' => true,
            ],
            [
                'code' => 'HEALTH_SCREEN',
                'name' => 'คัดกรองสุขภาพและป้องกันโรค (Health Screening)',
                'category' => 'ส่งเสริมและป้องกัน',
                'description' => 'ตรวจสุขภาพประจำปี คัดกรองมะเร็งปากมดลูก ตรวจคัดกรองวัณโรค',
                'is_active' => true,
            ],
            [
                'code' => 'VACCINE',
                'name' => 'สร้างเสริมภูมิคุ้มกันโรค (EPI & Vaccination)',
                'category' => 'ส่งเสริมและป้องกัน',
                'description' => 'ฉีดวัคซีนเด็กแรกเกิด วัคซีนไข้หวัดใหญ่ วัคซีนบาดทะยัก',
                'is_active' => true,
            ],
            [
                'code' => 'DENTAL_CARE',
                'name' => 'บริการทันตกรรม (Dental Services)',
                'category' => 'ทันตกรรม',
                'description' => 'ตรวจสุขภาพช่องปาก ขูดหินปูน ถอนฟัน อุดฟัน เคลือบหลุมร่องฟัน',
                'is_active' => true,
            ],
            [
                'code' => 'REHAB',
                'name' => 'ฟื้นฟูสมรรถภาพกายภาพบำบัด (Physical Therapy)',
                'category' => 'ฟื้นฟูสมรรถภาพ',
                'description' => 'ดึงคอ ดึงหลัง อัลตราซาวนด์ ฝึกเดิน ฟื้นฟูอัมพฤกษ์อัมพาต',
                'is_active' => true,
            ],
            [
                'code' => 'THAI_MED',
                'name' => 'การแพทย์แผนไทยและการฝังเข็ม (Traditional Medicine)',
                'category' => 'การแพทย์ทางเลือก',
                'description' => 'นวดรักษา ประคบสมุนไพร อบไอน้ำสมุนไพร จ่ายยาแผนไทย',
                'is_active' => true,
            ],
            [
                'code' => 'LAB_TEST',
                'name' => 'ตรวจชันสูตรทางห้องปฏิบัติการ (Lab Diagnostics)',
                'category' => 'ชันสูตร',
                'description' => 'ตรวจเลือด ปัสสาวะ อุจจาระ ตรวจความสมบูรณ์ของเม็ดเลือด',
                'is_active' => true,
            ],
            [
                'code' => 'DISPENSE',
                'name' => 'บริการเภสัชกรรมและจ่ายยา (Pharmacy Dispensing)',
                'category' => 'เภสัชกรรม',
                'description' => 'จ่ายยาตามใบสั่งแพทย์ และให้คำแนะนำการใช้ยา',
                'is_active' => true,
            ],
            [
                'code' => 'INPATIENT_CARE',
                'name' => 'การดูแลรักษาผู้ป่วยใน (Inpatient Admission)',
                'category' => 'ผู้ป่วยใน',
                'description' => 'นอนพักรักษาตัวในหอผู้ป่วย',
                'is_active' => true,
            ],
        ];

        foreach ($services as $serv) {
            ServiceType::updateOrCreate(['code' => $serv['code']], $serv);
        }
    }
}

