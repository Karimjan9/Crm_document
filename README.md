# CRM Document

CRM Document — filiallar, xodimlar va administratorlar uchun hujjat qabul qilish, xizmat narxini hisoblash, to‘lov va fayl boshqaruvi tizimi.

## Texnologiyalar

- PHP 8.2+ va Laravel 12
- MySQL/MariaDB
- Laravel Sanctum token API
- Spatie Laravel Permission
- Vite, npm va legacy Blade asset bundle

## Rollar

- `super_admin` — tizim konfiguratsiyasi va barcha filiallar
- `admin_manager` — boshqaruv operatsiyalari
- `admin_filial` — o‘z filialidagi hujjatlar va to‘lovlar
- `employee` — o‘z hujjatlari va mijozlari
- `courier` — biriktirilgan hujjatlar

Hujjat va fayl endpointlarida policy hamda filial/egalik scope ishlaydi. Hujjat fayllari private diskda saqlanadi; download faqat authorization endpointi orqali amalga oshadi.

## Lokal o‘rnatish

```bash
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm ci
npm run build
php artisan storage:link
php artisan serve
```

`.env` ichida kamida `APP_URL`, `APP_TIMEZONE`, database sozlamalari va kerak bo‘lsa `SMS_LOGIN`/`SMS_PASSWORD` qiymatlarini kiriting. Ishlab chiqarishda `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL` va `SESSION_SECURE_COOKIE=true` bo‘lishi shart.

## API

Eski Blade sahifalari ishlatadigan `/admin/api`, `/employee/api`, `/admin_filial/api` va `/superadmin/api` URL’lari backward compatibility uchun session/web middleware bilan saqlangan.

Yangi tashqi API `/api/v1` prefiksida va Sanctum Bearer token bilan ishlaydi:

```http
POST /api/v1/auth/token
Content-Type: application/json

{"login":"...","password":"...","device_name":"mobile"}
```

Javobdagi tokenni `Authorization: Bearer <token>` headerida yuboring. Asosiy endpointlar: `/api/v1/user`, `/api/v1/clients`, `/api/v1/documents`, `/api/v1/documents/batch` va `/api/v1/document-addons/{type}/{id}`.

Cross-origin foydalanish uchun production `.env`da `CORS_ALLOWED_ORIGINS`ga faqat aniq HTTPS originlarni vergul bilan yozing. Wildcard va credential cookie rejimi yoqilmagan.

## Test va tekshiruv

```bash
php artisan test
composer audit
npm audit --audit-level=high
npm run build
php artisan route:cache
php artisan view:cache
```

## Production deploy

Production serverda `git pull` ishlatilmaydi. `deploy.sh` worktree tozaligini tekshiradi, remote’dan fetch qiladi, aniq commitni tanlaydi, maintenance mode yoqadi, database va storage backup yaratadi, dependency/frontend buildni bajaradi, migration’dan keyin cache va storage linkni tekshiradi hamda `/health` endpoint orqali smoke-check qiladi.

```bash
bash deploy.sh
```

Kerakli production tool’lar: `git`, `php`, `composer`, `node`, `npm`, `curl`, `tar` va MySQL/MariaDB uchun `mysqldump`. `.env`da `DEPLOY_HEALTH_URL`ni to‘liq HTTPS URL bilan ko‘rsatish tavsiya etiladi; u bo‘lmasa `${APP_URL}/health` ishlatiladi. `DEPLOY_REF` orqali tag yoki commit berib, takrorlanadigan deploy/rollback qilish mumkin.

Deploy migration’dan oldin `storage/app/backups` ichida SQL dump va mavjud `storage/app/private`/`storage/app/public` kataloglarining archive nusxasini yaratadi. Xato yuz bersa maintenance mode o‘chiriladi va aniq rollback buyrug‘i chiqariladi. Rollback avtomatik qilinmaydi, chunki ishlab turgan database’dagi yangi yozuvlarni ko‘r-ko‘rona eski dump bilan almashtirish xavfli:

```bash
CONFIRM_ROLLBACK=YES \
ROLLBACK_COMMIT=<old-commit> \
DB_BACKUP_FILE=storage/app/backups/<dump>.sql \
STORAGE_BACKUP_FILE=storage/app/backups/<storage>.tar.gz \
bash rollback.sh
```

Rollback default holatda database’ni qayta yaratib, dump’ni toza holatda import qiladi. Agar production DB user’ida `DROP/CREATE DATABASE` huquqi bo‘lmasa, oldindan `ROLLBACK_RECREATE_DATABASE=false` berib, importni mavjud database ichida bajaring.

`QUEUE_CONNECTION=database` production default hisoblanadi. Queue jadvali migration bilan yaratiladi; worker Supervisor orqali doimiy ishlashi kerak. Tayyor namuna: `deploy/supervisor/crm-document-worker.conf`. Scheduler uchun `/etc/cron.d/` namuna: `deploy/cron/crm-document-scheduler`. Har deploydan keyin `php artisan queue:restart` workerlarni yangi kod bilan qayta yuklaydi.

`backup:database` command deploy’dan tashqari qo‘lda ham ishlaydi:

```bash
php artisan backup:database --keep=7
```

Super-admin panelidagi Excel va SQL backup amallari faqat queue orqali bajariladi: Excel’da `POST /superadmin/excel/{dataset}/queue`, SQL backup’da `POST /superadmin/monthly-notifications/sql-backup/queue`; status va download URL javobda qaytariladi. Bu endpointlar `QUEUE_CONNECTION=database` worker orqali bajariladi, shuning uchun og‘ir operatsiya HTTP request ichida sinxron bajarilmaydi.

## Frontend assetlar

Faol sahifalar hozirgi Blade asset bundle’ini saqlab qoladi. Vite entrypointlari (`resources/css/app.css`, `resources/js/app.js`) build pipeline uchun tayyor; production deploy `npm ci --include=dev` va `npm run build`ni bajaradi. `public/assets` hozirgi faol legacy bundle; `public/assets_2` eski, ishlatilmayotgan bundle bo‘lib, fayllari to‘liq bir xil emasligi sabab ko‘r-ko‘rona o‘chirilmagan.

`public/assets_2` hozir `template_2` va eski header partial tomonidan ishlatiladi. Ularni yangi `public/assets` bundle’iga ko‘chirish va keyin duplicate katalogni o‘chirish alohida regression bosqichi sifatida qoldirilgan; hozircha o‘chirish eski layoutlarni buzishi mumkin.
## B2B partner platform

The CRM now supports a separate B2B partner tenant layer:

- partner company profile, type, corporate discount, payment terms and allowed branches;
- partner cabinet for orders, statistics, API keys, invoices and white-label tracking;
- partner API at `/api/v1/partner` using a hashed `Bearer b2b_...` key;
- package catalog at `GET /api/v1/partner/catalog?filial_id=...`;
- order create/list/detail at `/api/v1/partner/orders`;
- monthly invoice generation with `php artisan b2b:generate-invoices --period=YYYY-MM`.

Create and assign a partner from `admin/partners`. A partner must have at least one allowed branch before it can create an order.
