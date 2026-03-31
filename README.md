# FlowCRM — action-driven CRM (Laravel backend)

**FlowCRM** is a customer relationship tool built around **“direct action, not just logging”**: it reduces data-entry fatigue and the gap between records and follow-up. The database feeds a daily **action stream** tied to how reps work on **LINE OA**.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About the project

The backend is **Laravel**, with APIs and Blade views for managers/admins. See the official Laravel docs for routing, Eloquent, migrations, and broadcasting.

## System architecture

Designed for **multi-tenant SaaS** and **event-driven** workflows:

* **Front-end (Sales):** SvelteKit + Vite + Tailwind (`flow-crm-frontend` / `develop/flow-crm-frontend`)
* **Back-end:** Laravel (API + Blade) — business logic and per-organization data
* **Workflow engine:** **n8n** — LINE OA webhooks and automation
* **Data:** MySQL (via Laravel Sail in development)
* **Services (Sail):** MySQL, Redis, Mailpit

---

## Quick start (Laravel Sail)

1. **Clone the repo**

   ```bash
   git clone <repository-url>.git
   cd flow-crm-backend
   ```

2. **Configure `.env`**

   ```bash
   cp .env.example .env
   ```

3. **Install PHP dependencies (Composer)**

   ```bash
   docker run --rm \
     -u "$(id -u):$(id -g)" \
     -v "$(pwd):/app" \
     -w /app \
     composer:latest \
     composer install --ignore-platform-reqs
   ```

   Or run `composer install` locally if PHP/Composer are installed.

4. **Start containers (MySQL, Redis, Mailpit, app)**

   ```bash
   ./vendor/bin/sail up -d
   ```

5. **App key, migrations, and sample data**

   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ```

6. **Open the app** — default `http://localhost` (port from `APP_PORT` in `.env`).

### `.env` aligned with Sail (MySQL)

If you use Sail’s `compose.yaml`, set values similar to below (full list in `.env.example`):

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

### Initialize / refresh database

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:refresh --seed
```

* **Mailpit UI:** `http://localhost:8025` (see `FORWARD_MAILPIT_DASHBOARD_PORT`)
* **Manager registration:** `http://localhost/register`
* **Sales signup:** frontend `/register` with organization invite code

Stop containers: `./vendor/bin/sail down`

### Prerequisites

* [Docker Desktop](https://www.docker.com/products/docker-desktop/)

### Optional: Sail + front-end dev

```bash
./vendor/bin/sail yarn install
./vendor/bin/sail yarn dev
```

## Access (sample credentials)

After seeding:

* **Admin:** `admin@flowcrm.com` / `password`
* **Manager org1:** `manager@org1.com` / `password`
* **Manager org2:** `manager@org2.com` / `password`
* **Sales:** `sales1@org1.com` / `password`
* **Sales:** `sales1@org2.com` / `password`

Sales app (SvelteKit) and **n8n**: see `flow-crm-frontend/README.md` and `flow-crm-n8n/README.md`.

## Key features (demo)

* **Action stream** — prioritized tasks per day
* **Sales pipeline** — Kanban-style stages
* **LINE** — copy scripts and deep links to chat

## License

Open-source under the [MIT License](https://opensource.org/licenses/MIT).
