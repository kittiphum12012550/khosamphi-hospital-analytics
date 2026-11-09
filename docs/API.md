# Khosamphi Hospital Analytics - API Documentation
## คู่มือการเชื่อมต่อระบบภายนอกและการพัฒนาระบบต่อยอด (API Integration Guide)

ระบบ Khosamphi Hospital Analytics Dashboard ได้รับการออกแบบสถาปัตยกรรมให้พร้อมรองรับการเชื่อมต่อกับระบบสารสนเทศโรงพยาบาล (Hospital Information System - HIS) เช่น HosXP, Jhcis หรือระบบคิวอัตโนมัติ

---

### 1. การยืนยันตัวตน (Authentication)
ระบบใช้ **Laravel Sanctum** สำหรับ API Token Authentication
- **Header**: `Authorization: Bearer <API_TOKEN>`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

---

### 2. Endpoints ที่มีพร้อมใช้งาน

#### 2.1 ตรวจสอบสถานะระบบ (Health Check)
```http
GET /api/v1/health
```
**Response (200 OK):**
```json
{
  "status": "healthy",
  "hospital": "โรงพยาบาลโกสัมพีนคร",
  "timestamp": "2026-11-09T13:20:00+07:00"
}
```

---

#### 2.2 ดึงรายชื่อแผนกที่เปิดใช้งาน (Get Active Departments)
```http
GET /api/v1/departments
```
**Response (200 OK):**
```json
[
  {
    "id": 1,
    "code": "OPD",
    "name_th": "แผนกผู้ป่วยนอก (OPD)",
    "name_en": "Outpatient Department",
    "location": "อาคารผู้ป่วยนอก ชั้น 1",
    "phone": "101, 102",
    "is_active": true
  },
  {
    "id": 2,
    "code": "ER",
    "name_th": "แผนกอุบัติเหตุและฉุกเฉิน (ER)",
    "name_en": "Emergency Room",
    "location": "อาคารอุบัติเหตุ ชั้น 1",
    "phone": "1669, 111",
    "is_active": true
  }
]
```

---

#### 2.3 ดึงข้อมูลประเภทบริการ (Get Active Service Types)
```http
GET /api/v1/service-types
```
**Response (200 OK):**
```json
[
  {
    "id": 1,
    "code": "GEN_EXAM",
    "name": "ตรวจรักษาโรคทั่วไป (General Consultation)",
    "category": "รักษาพยาบาล",
    "is_active": true
  },
  {
    "id": 2,
    "code": "EMERGENCY",
    "name": "อุบัติเหตุและฉุกเฉิน (Emergency & Trauma)",
    "category": "ฉุกเฉิน",
    "is_active": true
  }
]
```

---

#### 2.4 ดึงข้อมูลสถิติสำหรับ Dashboard แบบ Real-Time (Internal AJAX API)
```http
GET /dashboard/analytics-data?department_id=1&year=2024
```
**Response (200 OK):**
```json
{
  "summary": {
    "total_visits": 14250,
    "today_visits": 128,
    "this_month_visits": 2340,
    "top_department_name": "OPD - แผนกผู้ป่วยนอก (OPD)",
    "top_department_count": 6820,
    "active_departments": 10,
    "avg_daily_visits": 118.5
  },
  "daily_trend": {
    "labels": ["1 พ.ย. 2567", "2 พ.ย. 2567"],
    "data": [135, 142]
  },
  "department_breakdown": {
    "labels": ["OPD", "ER", "LAB", "PHARM"],
    "data": [6820, 2140, 3100, 4200]
  },
  "service_distribution": {
    "labels": ["ตรวจรักษาโรคทั่วไป", "อุบัติเหตุและฉุกเฉิน"],
    "data": [6820, 2140]
  },
  "monthly_comparison": {
    "labels": ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย."],
    "current_year": 2567,
    "current_data": [2100, 2250, 2400, 2150],
    "previous_year": 2566,
    "previous_data": [1950, 2050, 2200, 2000]
  },
  "hourly_distribution": {
    "labels": ["00:00", "01:00", "...", "23:00"],
    "data": [2, 1, 45, 12]
  }
}
```

---

### 3. ตัวอย่างการส่งข้อมูลสถิติอัตโนมัติจาก HIS ประจำวัน (HIS Cron Integration)
สามารถพัฒนาคำสั่ง Artisan Command หรือ Webhook ส่งข้อมูลสรุปรายวันจากระบบเวชระเบียนโรงพยาบาล เข้าสู่ฐานข้อมูล `visit_statistics` ด้วย `source_type = 'api'`

