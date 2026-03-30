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

* **Front-end (Sales):** SvelteKit + Vite + Tailwind (repo `flow-crm-frontend`)
* **Back-end:** Laravel (API + Blade) จัดการ Business Logic และการแยกข้อมูลตามองค์กร
* **Workflow Engine:** **n8n** ทำหน้าที่เป็นสมองกลคอยรับ Webhook จาก LINE OA และแปลงสัญญาณพฤติกรรมลูกค้าให้กลายเป็นกิจกรรมในระบบอัตโนมัติ 


* **Data Layer:** MySQL (ผ่าน Laravel Sail ในโหมดพัฒนา)
* **Services:** Docker (Sail) — MySQL, Redis, Mailpit

---

## Quick Start — รัน Backend (Laravel Sail)

1. **โคลน repo**
   ```bash
   git clone <url-ของ-repo-นี้>.git
   cd flow-crm-backend
   ```
2. **ตั้งค่า `.env`**
   ```bash
   cp .env.example .env
   ```
3. **ติดตั้ง dependency PHP (Composer)**
   ```bash
   docker run --rm \
     -u "$(id -u):$(id -g)" \
     -v "$(pwd):/app" \
     -w /app \
     composer:latest \
     composer install --ignore-platform-reqs
   ```
   *หรือรัน `composer install` ในโฟลเดอร์โปรเจกต์ถ้ามี PHP/ Composer บนเครื่องแล้ว*
4. **ขึ้น container (MySQL, Redis, Mailpit, app)**
   ```bash
   ./vendor/bin/sail up -d
   ```
5. **สร้าง key และฐานข้อมูล + seed ตัวอย่าง**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ```
6. **เปิดเว็บ** — โดยค่าเริ่มต้น `http://localhost` (พอร์ต 80 ตาม `APP_PORT` ใน `.env`)

### ค่า `.env` ที่ควรตรงกับ Sail (MySQL)

ถ้าใช้ `compose.yaml` ของ Sail ให้กำหนดประมาณนี้ (ดูรายละเอียดเต็มใน `.env.example`):

```env
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

REDIS_HOST=redis
```

- **Mailpit UI:** `http://localhost:8025` (ตาม `FORWARD_MAILPIT_DASHBOARD_PORT`)
- **ผู้จัดการลงทะเบียน:** `http://localhost/register`  
- **Sales สมัคร:** ผ่านแอป frontend `/register` ด้วยรหัสเชิญองค์กร

รันคำสั่ง artisan ซ้ำๆ: `./vendor/bin/sail artisan ...`  
หยุด: `./vendor/bin/sail down`

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (แนะนำ WSL2 บน Windows)


## Access Information (ข้อมูลการเข้าใช้งาน)

เมื่อรันระบบเสร็จสิ้น สามารถเข้าใช้งานได้ที่: `http://localhost` สำหรับ Role Manager และ Admin

**บัญชีสำหรับทดสอบ (Default Credentials):**

* **Admin:** `admin@flowcrm.com` / `password`
* **Manager-Org1:** `manager@org1.com` / `password`
* **Manager-Org2:** `manager@org2.com` / `password`
* **Sales:** `sales1@org1.com` / `password`
* **Sales:** `sales1@org2.com` / `password`

แอป Sales (SvelteKit) และ workflow **n8n** — ดู `flow-crm-frontend/README.md` และ `flow-crm-n8n/README.md`

## Key Features for Demo

* **Action Stream:** ดูรายการงานที่ระบบสั่งการให้ทำในแต่ละวัน 


* **Sales Pipeline:** กระดาน Kanban แบบ Sequential ที่เน้นความต่อเนื่องของดีล 


* **LINE Integration:** ปุ่ม Copy Script และ Deep Link เพื่อเปิดห้องแชทลูกค้าทันที 



## License

The FlowCRM project is open-sourced software licensed under the([https://opensource.org/licenses/MIT](https://opensource.org/licenses/MIT)).
