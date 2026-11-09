<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'code' => 'OPD',
                'name_th' => 'แผนกผู้ป่วยนอก (OPD)',
                'name_en' => 'Outpatient Department',
                'description' => 'บริการตรวจรักษาโรคทั่วไปและคลินิกเฉพาะทางผู้ป่วยนอก',
                'phone' => '101, 102',
                'location' => 'อาคารผู้ป่วยนอก ชั้น 1',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'ER',
                'name_th' => 'แผนกอุบัติเหตุและฉุกเฉิน (ER)',
                'name_en' => 'Emergency Room',
                'description' => 'บริการกู้ชีพ ฉุกเฉิน อุบัติเหตุตลอด 24 ชั่วโมง',
                'phone' => '1669, 111',
                'location' => 'อาคารอุบัติเหตุ ชั้น 1',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'IPD',
                'name_th' => 'แผนกผู้ป่วยใน (IPD)',
                'name_en' => 'Inpatient Department',
                'description' => 'บริการหอผู้ป่วยสามัญและห้องพิเศษ รองรับ 30 เตียง',
                'phone' => '201, 202',
                'location' => 'อาคารผู้ป่วยใน ชั้น 2',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'DENT',
                'name_th' => 'กลุ่มงานทันตกรรม',
                'name_en' => 'Dental Clinic',
                'description' => 'บริการทันตกรรมป้องกันและทันตกรรมรักษา',
                'phone' => '130',
                'location' => 'อาคารบริการ ชั้น 1',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'PT',
                'name_th' => 'งานกายภาพบำบัดและฟื้นฟู',
                'name_en' => 'Physical Therapy Unit',
                'description' => 'บริการฟื้นฟูสมรรถภาพร่างกายและกายภาพบำบัดผู้สูงอายุ/ผู้ป่วยหลอดเลือดสมอง',
                'phone' => '135',
                'location' => 'อาคารบริการ ชั้น 2',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'code' => 'TTM',
                'name_th' => 'งานแพทย์แผนไทยและการแพทย์ทางเลือก',
                'name_en' => 'Thai Traditional Medicine',
                'description' => 'บริการตรวจรักษา นวด ประคบ อบสมุนไพร และจ่ายยาสมุนไพร',
                'phone' => '138',
                'location' => 'เรือนแพทย์แผนไทย',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'code' => 'LAB',
                'name_th' => 'กลุ่มงานเทคนิคการแพทย์ (ห้องแล็บ)',
                'name_en' => 'Clinical Laboratory',
                'description' => 'บริการตรวจวิเคราะห์ทางห้องปฏิบัติการ โลหิตวิทยา เคมีคลินิก',
                'phone' => '140',
                'location' => 'อาคารผู้ป่วยนอก ชั้น 1',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'PHARM',
                'name_th' => 'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',
                'name_en' => 'Pharmacy Department',
                'description' => 'บริการจ่ายยาและให้คำปรึกษาการใช้ยาอย่างสมเหตุผล',
                'phone' => '150',
                'location' => 'ห้องจ่ายยาผู้ป่วยนอก ชั้น 1',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'code' => 'NCD',
                'name_th' => 'คลินิกโรคเรื้อรัง (NCD Clinic)',
                'name_en' => 'Non-Communicable Diseases Clinic',
                'description' => 'ตรวจติดตามดูแลผู้ป่วยเบาหวาน ความดันโลหิตสูง โรคไต',
                'phone' => '105',
                'location' => 'อาคารผู้ป่วยนอก ชั้น 2',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'code' => 'LR',
                'name_th' => 'ห้องคลอดและทารกแรกเกิด (Labor Room)',
                'name_en' => 'Labor and Delivery Room',
                'description' => 'บริการทำคลอดและดูแลทารกแรกเกิด',
                'phone' => '210',
                'location' => 'อาคารบริการ ชั้น 2',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}

