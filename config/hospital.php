<?php

return [
    'name_th' => env('HOSPITAL_NAME_TH', 'โรงพยาบาลโกสัมพีนคร'),
    'name_en' => env('HOSPITAL_NAME_EN', 'Khosamphi Nakhon Hospital'),
    'province' => env('HOSPITAL_PROVINCE', 'กำแพงเพชร'),
    'bed_capacity' => env('HOSPITAL_BED_CAPACITY', 30),
    'max_import_size_kb' => env('MAX_IMPORT_FILE_SIZE_KB', 10240),
    'service_hours' => [
        '00:00 - 07:59' => 'เวรดึก (Night Shift)',
        '08:00 - 11:59' => 'เวรเช้า - เช้า (Morning Peak)',
        '12:00 - 15:59' => 'เวรเช้า - บ่าย (Afternoon)',
        '16:00 - 19:59' => 'เวรบ่าย - คลินิกนอกเวลา (Extended Clinic)',
        '20:00 - 23:59' => 'เวรบ่าย - ดึก (Evening/Night)',
    ],
    'roles' => [
        'admin' => 'ผู้ดูแลระบบ (Admin)',
        'staff' => 'เจ้าหน้าที่บันทึกข้อมูล (Staff)',
        'viewer' => 'ผู้ดูรายงาน (Viewer)',
    ],
    'sources' => [
        'manual' => 'บันทึกด้วยตนเอง (Manual)',
        'excel_import' => 'นำเข้า Excel',
        'csv_import' => 'นำเข้า CSV',
        'api' => 'ระบบภายนอก (API/HIS)',
    ]
];

