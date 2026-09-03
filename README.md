# AutoCare Pro — Car Wash & Vehicle Service Center Management System

Laravel + Blade + MySQL production-oriented starter implementing the core operational lifecycle requested in the supplied master specification: customers, multiple vehicles, appointments, job cards, digital inspection, damage/photo capture, service approval, inventory movements, parts consumption, invoicing, split/partial-ready payments, live job board, dashboard, reporting, role-aware users, communication logging, audit-ready transactional design, and responsive automotive UI.

## Requirements
- PHP 8.2+
- Composer 2+
- MySQL 8+
- Node.js 20+ (optional for asset pipeline)

## Install
1. `composer install`
2. `cp .env.example .env`
3. Configure MySQL in `.env`.
4. `php artisan key:generate`
5. `php artisan migrate --seed`
6. `php artisan storage:link`
7. `php artisan serve`

Optional frontend build:
`npm install && npm run build`

## Demo login
- admin@autocare.local / password
- owner@autocare.local / password
- reception@autocare.local / password
- tech@autocare.local / password
- cashier@autocare.local / password

## Core routes
- `/dashboard`
- `/jobs/board`
- `/jobs`
- `/customers`
- `/vehicles`
- `/appointments`
- `/inventory`
- `/invoices`
- `/reports`

## Architecture notes
Transactional operations are placed in domain services rather than controllers. Inventory changes create movement records. Payments are separate from invoices. Job statuses are constrained through the JobService state transition map. The schema includes business/branch tenancy fields so branch-aware authorization can be extended without rewriting transactional entities.

## WhatsApp
The communication service is intentionally provider-safe. It logs messages when no provider is configured and supports Meta WhatsApp Cloud API when `WHATSAPP_PROVIDER=meta` and the corresponding credentials are configured. No unofficial WhatsApp Web automation is used.

## Important
This archive is a generated Laravel source project. The environment used to build the archive does not have Composer/network access, so `vendor/` is intentionally not bundled. Run `composer install` on a machine with Composer and network/package-cache access before first launch.
