# Khosamphi Hospital Analytics Dashboard (โรงพยาบาลโกสัมพีนคร)

ระบบสารสนเทศวิเคราะห์สถิติการเข้ารับบริการของผู้ป่วยสำหรับโรงพยาบาลโกสัมพีนคร จ.กำแพงเพชร พัฒนาด้วย Laravel 10, PHP 8.1+, MySQL, Bootstrap 5 และ Chart.js

---

## 🏥 ข้อมูลภาพรวมของระบบ (System Overview)

ระบบถูกออกแบบสำหรับติดตาม วิเคราะห์ และรายงานจำนวนครั้งของการเข้ารับบริการของผู้ป่วย (Visits) ในแต่ละแผนกและประเภทบริการ โดยคำนึงถึงความปลอดภัยของข้อมูล (Data Privacy) ใช้ข้อมูลสังเคราะห์ (Synthetic Data) และไม่เก็บข้อมูลที่สามารถระบุตัวตนผู้ป่วยได้ (Non-PII)

### คุณสมบัติหลัก (Key Features)
1. **Interactive Dashboard**:
   - บัตรสรุปตัวชี้วัด (KPI Cards): ยอดรับบริการรวม, วันนี้, เดือนนี้, แผนกที่มีผู้รับบริการสูงสุด, ค่าเฉลี่ยต่อวัน
   - กราฟเส้นแนวโน้มรายวัน (Daily Trend Line Chart)
   - กราฟแท่งเปรียบเทียบจำนวนครั้งแยกตามแผนก (Department Bar Chart)
   - กราฟวงแหวนสัดส่วนประเภทบริการ (Service Type Doughnut Chart)
   - กราฟเปรียบเทียบสถิติรายเดือนระหว่างปีปัจจุบันและปีก่อนหน้า (Monthly Comparison)
   - กราฟวิเคราะห์ช่วงเวลาหนาแน่น 00:00 - 23:00 น. (Peak Congestion Hours)
   - ตัวกรองขั้นสูง: ช่วงวันที่เริ่มต้น-สิ้นสุด, แผนก, บริการ, ปี พ.ศ., เดือน
2. **ระบบจัดการข้อมูลสถิติ (Visit Statistics CRUD)**:
   - บันทึก แก้ไข ค้นหา และลบข้อมูลสถิติรายชั่วโมง
   - ตรวจสอบความถูกต้องและป้องกันการบันทึกข้อมูลซ้ำซ้อน
3. **ระบบนำเข้าไฟล์ CSV และ Excel (Import Workflow)**:
   - ตรวจสอบความถูกต้องของโครงสร้างไฟล์ล่วงหน้า (Pre-flight Validation)
   - หน้าจอแสดงผลตัวอย่าง (Preview) แสดงจำนวนแถวที่ถูกต้องและแถวที่มีข้อผิดพลาดแบบระบุเลขแถว
   - ตรวจสอบ SHA-256 File Hash ป้องกันการนำเข้าไฟล์เดิมซ้ำ
   - บันทึกด้วย Database Transaction และระบบชุดนำเข้า (Import Batches)
   - ความสามารถในการยกเลิกและย้อนกลับชุดข้อมูล (Rollback Batch)
4. **ระบบรายงานและการส่งออก (Reports & Export)**:
   - รายงานสรุปรายวัน, รายเดือน, รายปี, แยกตามแผนก และแยกตามประเภทบริการ
   - ส่งออกข้อมูลเป็นไฟล์ Excel / CSV (รองรับภาษาไทยด้วย UTF-8 BOM)
   - ส่งออกเอกสาร PDF ด้วย DomPDF พร้อมแบบฟอร์มลงนามทางการแพทย์
   - โหมดพิมพ์รายงาน (Print View)
5. **ระบบสมาชิกและสิทธิ์การใช้งาน (RBAC 3 Levels)**:
   - **Admin**: จัดการผู้ใช้งาน, จัดการแผนก, จัดการประเภทบริการ, ดู Audit Logs, ทำได้ทุกฟังก์ชัน
   - **Staff**: บันทึกสถิติ, นำเข้าไฟล์ CSV/Excel, ดู Dashboard และตรวจสอบประวัติตนเอง
   - **Viewer**: ดูแดชบอร์ด รายงาน และส่งออกข้อมูล (Read-Only)
6. **ระบบประวัติการทำรายการ (Audit Logs)**:
   - บันทึกทุกกิจกรรม: เข้าสู่ระบบ, ออกจากระบบ, สร้าง, แก้ไข, ลบ, นำเข้า, ส่งออก, ย้อนกลับ
   - แสดงการเปลี่ยนแปลงค่าเดิมและค่าใหม่ (Old/New Payload Diff) พร้อม IP และ User-Agent

---

## 🔑 บัญชีผู้ใช้เริ่มต้นสำหรับทดสอบ (Default Demo Accounts)

| ระดับสิทธิ์ (Role) | อีเมล (Email) | รหัสผ่าน (Password) | สิทธิ์การเข้าถึง |
|---|---|---|---|
| **Admin** | `admin@khosamphi.go.th` | `admin1234` | ครบทุกโมดูล (ผู้ใช้, แผนก, บริการ, Audit Logs) |
| **Staff** | `staff@khosamphi.go.th` | `staff1234` | บันทึกสถิติ, นำเข้า CSV/Excel, ดูแดชบอร์ด |
| **Viewer** | `viewer@khosamphi.go.th` | `viewer1234` | ดูแดชบอร์ด, ดูกราฟ, ส่งออกรายงาน (Read-only) |

*(หน้าจอ Login มีปุ่มคลิกเดียวเพื่อกรอกข้อมูลบัญชีทดสอบอัตโนมัติ)*

---

## 🚀 วิธีการติดตั้งและรันระบบในเครื่อง (Local Setup Guide)

### วิธีที่ 1: รันด้วย Docker & Docker Compose (แนะนำ สะดวกรวดเร็วที่สุด)

1. ติดตั้ง Docker Desktop
2. เปิด Terminal ในโฟลเดอร์โปรเจกต์:
   ```bash
   docker-compose up -d --build
   ```
3. เมื่อ Container เริ่มทำงาน:
   - เว็บไซต์ระบบ: [http://localhost:8080](http://localhost:8080)
   - เครื่องมือจัดการฐานข้อมูล phpMyAdmin: [http://localhost:8081](http://localhost:8081)
     *(User: `root`, Password: `root_secret_password`)*

---

### วิธีที่ 2: ติดตั้งด้วย PHP, Composer และ MySQL (Manual Setup)

1. **คัดลอกไฟล์ Environment**:
   ```bash
   copy .env.example .env
   ```
2. **แก้ไขการเชื่อมต่อฐานข้อมูลใน `.env`**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=khosamphi_hospital_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. **ติดตั้ง PHP Dependencies ผ่าน Composer**:
   ```bash
   composer install
   ```
4. **สร้าง Application Encryption Key**:
   ```bash
   php artisan key:generate
   ```
5. **รัน Migration และ Seeder ข้อมูลสังเคราะห์**:
   ```bash
   php artisan migrate:fresh --seed
   ```
6. **สตาร์ทเว็บเซิร์ฟเวอร์**:
   ```bash
   php artisan serve
   ```
7. เข้าใช้งานระบบที่: `http://localhost:8000`

---

## ☁️ การ Deploy ไปยัง Render.com ด้วย Docker

โปรเจกต์นี้จัดเตรียมไฟล์ `Dockerfile`, `docker/apache.conf`, `docker/entrypoint.sh` และ `render.yaml` ไว้อย่างสมบูรณ์ รองรับข้อกำหนดของ Render:
- Bind Server กับ `0.0.0.0` และพอร์ตแบบ Dynamic ผ่านตัวแปร `$PORT`
- กำหนด Document Root ไปที่ `/var/www/html/public`
- รันคำสั่ง `php artisan migrate --force` และ `php artisan db:seed --force` อัตโนมัติเมื่อ Container เริ่มทำงาน

### ขั้นตอนการ Deploy:
1. Push โค้ดทั้งหมดขึ้น GitHub Repository ส่วนตัวหรือขององค์กร
2. เข้าสู่ระบบ [Render.com](https://render.com)
3. เลือก **New +** -> **Blueprint** แล้วเลือก Repository นี้ (Render จะอ่านค่าจาก `render.yaml` อัตโนมัติ)
4. หรือเลือก **New +** -> **Web Service** -> Docker:
   - **Region**: Singapore (ใกล้ประเทศไทย ให้ Latency ต่ำสุด)
   - **Environment Variables**:
     - `APP_ENV`: `production`
     - `APP_DEBUG`: `false`
     - `APP_KEY`: สร้างผ่าน `php artisan key:generate --show`
     - `DB_CONNECTION`: `mysql`
     - `DB_HOST`: Host ของ MySQL ภายนอก (เช่น Aiven, Railway, หรือ Clever Cloud)
     - `DB_PORT`: `3306`
     - `DB_DATABASE`: ชื่อฐานข้อมูล
     - `DB_USERNAME`: ผู้ใช้ฐานข้อมูล
     - `DB_PASSWORD`: รหัสผ่านฐานข้อมูล
     - `RUN_MIGRATIONS`: `true`
     - `SEED_DATABASE`: `true` (เฉพาะรอบแรกเพื่อสร้างผู้ใช้ Admin และ Master Data)

> **หมายเหตุสำคัญเรื่องฐานข้อมูลบน Render:**
> Render มี Managed PostgreSQL แต่ไม่มี Managed MySQL ในตัว สำหรับระบบนี้แนะนำให้เชื่อมต่อกับบริการ Free/Managed MySQL เช่น Aiven for MySQL หรือ Railway MySQL

---

## 📁 โครงสร้างโปรเจกต์ (Project Architecture)

```
d:\dashboard\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php       # ระบบยืนยันตัวตน
│   │   │   ├── DashboardController.php        # ประมวลผลแดชบอร์ดและกราฟ
│   │   │   ├── DepartmentController.php       # CRUD แผนก
│   │   │   ├── ServiceTypeController.php      # CRUD ประเภทบริการ
│   │   │   ├── VisitStatisticController.php   # CRUD สถิติการรับบริการ
│   │   │   ├── ImportController.php           # นำเข้าไฟล์ Preview & Rollback
│   │   │   ├── ReportController.php           # รายงาน Export Excel & PDF
│   │   │   ├── AuditLogController.php         # บันทึกประวัติและตรวจสอบ Diff
│   │   │   ├── UserController.php             # จัดการผู้ใช้งานและ Role
│   │   │   └── ProfileController.php          # จัดการข้อมูลส่วนตัว
│   │   ├── Middleware/
│   │   │   └── CheckRole.php                  # RBAC Middleware (Admin/Staff/Viewer)
│   │   └── Requests/                          # Form Request Validation Classes
│   ├── Models/                                # Eloquent Models & Scopes
│   └── Services/                              # Business Logic Layer
│       ├── AuditLogService.php                # บันทึกกิจกรรมอัตโนมัติ
│       ├── DashboardAnalyticsService.php      # คำนวณ KPI และ Chart Datasets
│       ├── ImportValidationService.php        # ตรวจสอบไฟล์ล่วงหน้า & Rollback
│       └── ReportService.php                  # ประมวลผลรายงานสถิติ
├── config/
│   └── hospital.php                           # การตั้งค่าเฉพาะ รพ.โกสัมพีนคร
├── database/
│   ├── migrations/                            # 9 Migrations โครงสร้างตาราง
│   └── seeders/                               # Seeders ข้อมูลสังเคราะห์สมบูรณ์
├── docker/
│   ├── apache.conf                            # VirtualHost & DocumentRoot
│   ├── entrypoint.sh                          # สคริปต์สตาร์ทและเชื่อมพอร์ต Render
│   └── php.ini                                # ตั้งค่าขนาดไฟล์และหน่วยความจำ
├── public/
│   ├── css/hospital-theme.css                 # สไตล์ชุดสีและ UI โรงพยาบาล
│   └── js/hospital-charts.js                  # สคริปต์วาดกราฟ Chart.js
├── resources/views/                           # Blade Templates ครบทุกหน้า
├── routes/
│   ├── web.php                                # เส้นทางระบบเว็บ
│   └── api.php                                # REST API รองรับอนาคต
├── Dockerfile                                 # Multi-stage production container
├── docker-compose.yml                         # Container พร้อมใช้งานในเครื่อง
└── render.yaml                                # Render.com Deployment Blueprint
```

#   k h o s a m p h i - h o s p i t a l - a n a l y t i c s  
 #   k h o s a m p h i - h o s p i t a l - a n a l y t i c s  
 