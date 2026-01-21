# FlowCRM: An Action-Driven CRM for Thai SMEs

**FlowCRM** เป็นระบบบริหารจัดการความสัมพันธ์ลูกค้าที่ออกแบบภายใต้ปรัชญา **"สั่งการ มากกว่า บันทึก"** เพื่อแก้ปัญหาหลักของ SME ไทย เช่น ความเหนื่อยล้าจากการป้อนข้อมูล (Data Entry Fatigue) และช่องว่างของการไม่ปฏิบัติงาน (Inaction Gap)  โดยเปลี่ยนฐานข้อมูลให้กลายเป็นระบบนำทางกิจกรรมรายวัน (Action Stream) ที่เชื่อมต่อกับพฤติกรรมการขายผ่าน LINE OA เป็นหลัก 

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About The Project

FlowCRM พัฒนาขึ้นโดยใช้เฟรมเวิร์ก Laravel ซึ่งเป็นเว็บแอปพลิเคชันเฟรมเวิร์กที่มีไวยากรณ์ที่แสดงออกถึงความหมายและสง่างาม เราเชื่อว่าการพัฒนาต้องเป็นประสบการณ์ที่สนุกสนานและสร้างสรรค์ Laravel ช่วยลดความยุ่งยากในงานทั่วไป เช่น:

-(https://laravel.com/docs/routing)

* [Powerful dependency injection container](https://laravel.com/docs/container)
-([https://laravel.com/docs/eloquent](https://laravel.com/docs/eloquent))
-([https://laravel.com/docs/migrations](https://laravel.com/docs/migrations))
-([https://laravel.com/docs/broadcasting](https://laravel.com/docs/broadcasting))

## System Architecture (สถาปัตยกรรมระบบ)

ระบบถูกออกแบบมาให้รองรับการขยายตัวแบบ Multi-tenant SaaS และทำงานแบบ Event-Driven :

* **Front-end:** React + Vite + Tailwind CSS เพื่อประสิทธิภาพการแสดงผลที่รวดเร็ว
* **Back-end:** Laravel 11 (REST API) จัดการ Business Logic และการแยกข้อมูลรายร้านค้า (Data Isolation)
* **Workflow Engine:** **n8n** ทำหน้าที่เป็นสมองกลคอยรับ Webhook จาก LINE OA และแปลงสัญญาณพฤติกรรมลูกค้าให้กลายเป็นกิจกรรมในระบบอัตโนมัติ 


* **Data Layer:** PostgreSQL (ฐานข้อมูลหลัก)
* **Services:** Docker สำหรับการจัดการสภาพแวดล้อม และ Mailpit สำหรับทดสอบระบบอีเมล

## Getting Started (ขั้นตอนการติดตั้ง)

### 1. Prerequisites

* ติดตั้ง([https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/))
* (สำหรับ Windows) แนะนำให้รันผ่าน WSL2

### 2. Installation

ทำการ Clone โปรเจกต์และเตรียมสภาพแวดล้อมดังนี้:

```bash
# Clone Github project
git clone https://github.com/WaritDev/flow-crm.git
cd flow-crm

# สร้างไฟล์.env จากตัวอย่าง
cp.env.example.env

```

**แก้ไขไฟล์ `.env**` กำหนดค่าพื้นฐานสำหรับการพัฒนา (Development):

```env
PHP_CLI_SERVER_WORKERS=4
LOG_CHANNEL=daily
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=lab_laravel
DB_USERNAME=sail
DB_PASSWORD=password

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

REDIS_HOST=redis

```

### 3. Install Dependencies & Application Initialization
ติดตั้งคอมโพเนนต์ที่จำเป็น, ตั้งค่ากุญแจความปลอดภัย และเตรียมฐานข้อมูลพร้อมข้อมูลตัวอย่าง (Seeder):

```bash
# ติดตั้ง PHP Dependencies (Composer) ผ่าน Docker
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/app" \
    -w /app \
    composer:latest \
    install --ignore-platform-reqs

# รัน Services ทั้งหมดด้วย Laravel Sail
./vendor/bin/sail up -d

# สร้าง APP_KEY
./vendor/bin/sail artisan key:generate

# Migration พร้อม Seed ข้อมูลตัวอย่าง (Manager, sales, Templates, Deals)
./vendor/bin/sail artisan migrate:refresh --seed

```

### 4. Start Services

```bash

# ติดตั้ง Front-end Dependencies และรัน Dev Server

./vendor/bin/sail sail yarn install
./vendor/bin/sail sail yarn dev

```

## Access Information (ข้อมูลการเข้าใช้งาน)

เมื่อรันระบบเสร็จสิ้น สามารถเข้าใช้งานได้ที่: `http://localhost`

**บัญชีสำหรับทดสอบ (Default Credentials):**

* **Admin:** `admin@flowcrm.com` / `password`
* **Manager-Org1:** `manager@org1.com` / `password`
* **Manager-Org2:** `manager@org2.com` / `password`
* **Sales:** `sales1@org1.com` / `password`
* **Sales:** `sales1@org2.com` / `password`

## Key Features for Demo

* **Action Stream:** ดูรายการงานที่ระบบสั่งการให้ทำในแต่ละวัน 


* **Sales Pipeline:** กระดาน Kanban แบบ Sequential ที่เน้นความต่อเนื่องของดีล 


* **LINE Integration:** ปุ่ม Copy Script และ Deep Link เพื่อเปิดห้องแชทลูกค้าทันที 



## License

The FlowCRM project is open-sourced software licensed under the([https://opensource.org/licenses/MIT](https://opensource.org/licenses/MIT)).
