# HealthHub — Laravel Backend (Full Port)

Porting lengkap platform **HealthHub — Modern Hospital Management & AI Healthcare** dari stack
Next.js/Drizzle ke **Laravel 12 REST API** (Eloquent ORM + Sanctum untuk autentikasi token).

| Domain | Endpoint Laravel |
|---|---|
| Auth (token, 8 peran RBAC) | `POST /api/auth/login`, `POST /api/auth/logout`, `POST /api/auth/register`, `GET /api/auth/me` |
| Lookup dokter/poli/jadwal/layanan | `GET /api/doctors`, `/api/polyclinics`, `/api/specializations`, `/api/services`, `/api/schedules` |
| Appointment (anti double-booking `slot_key`, auto antrean, reminder ≤2 jam) | `GET\|POST\|PATCH /api/appointments` |
| Antrean real-time (call/serve/complete/no_show + teks broadcast) | `GET\|POST /api/queues` |
| AI Triage (red-flag emergency guard, pemetaan spesialis, rekomendasi dokter) | `POST /api/ai/triage` |
| EMR SOAP + ICD-10 + tanda vital + e-resep + order lab + auto-invoice | `GET\|POST /api/medical-records`, `POST /api/vital-signs` |
| Break-glass access (audit permanen, UU PDP) | `POST /api/medical-records/break-glass` |
| Farmasi (antrean resep, dispensing, ledger stok, restock) | `/api/prescriptions*`, `/api/medicines` |
| Laboratorium (order, input hasil, auto flag ref-range, rilis + notifikasi) | `/api/lab-orders*`, `/api/lab-test-types` |
| Billing & pembayaran (invoice itemized, idempotency key, mock gateway) | `/api/invoices`, `POST /api/invoices/pay` |
| Rawat inap (bed board, admisi, transfer bed, discharge) | `/api/beds`, `/api/inpatients` |
| Pasien (profil, NIK hash, consent UU PDP) | `GET /api/patients` |
| CMS (artikel kesehatan), Notifikasi, Audit trail, Settings | `/api/articles`, `/api/notifications`, `/api/audit-logs`, `/api/settings` |

---

## 0. Perbaikan Penting (kenapa dulu “tidak muncul” di localhost)

Ini penyebab aplikasi tidak jalan/tidak tampil sebelumnya, semuanya sudah dibenahi:

| # | Masalah | Perbaikan |
|---|---|---|
| 1 | `vendor/` dan `.env` tidak ada di repo → `php artisan serve` fatal error (`vendor/autoload.php` tidak ditemukan) | Langkah setup di README §2 (composer install + salin `.env`) |
| 2 | `.env.example` memaksa **PostgreSQL** yang belum tentu jalan → migrasi gagal | Default kini **SQLite** (nol konfigurasi); PostgreSQL/MySQL tetap tersedia sebagai komentar |
| 3 | `APP_KEY` kosong → setiap halaman error *“No application encryption key has been specified”* | `php artisan key:generate` (wajib, ada di §2) |
| 4 | **Kelas model duplikat**: `app/Models/HealthHubModels.php` + `app/Models/{User,Doctor,Polyclinic,...}.php` → autoload ambigu, `Call to undefined method App\Models\Permission::roles()` saat seeding | Satu kelas = satu file PSR-4 (42 model di `app/Models/`), `HealthHubModels.php` dihapus, `classmap` dicabut dari `composer.json` |
| 5 | `ApiController` tidak punya `ok()`, `fail()`, `audit()`, `requireRole()` padahal dipakai **semua** controller → HTTP 500 | Helper ditambahkan di `app/Http/Controllers/ApiController.php` |
| 6 | `AuthController` masih kosong → tidak ada endpoint login | `login`, `logout`, `me`, `register` (token Sanctum) |
| 7 | `routes/api.php` hanya berisi 2 endpoint (`/doctors`, `/polyclinics`) | Seluruh endpoint didaftarkan + middleware `auth:sanctum` & `role:...` |
| 8 | Middleware `role` belum di-alias dan error `/api/*` keluar sebagai HTML | Alias didaftarkan di `bootstrap/app.php`; `ForceJsonResponse` + `shouldRenderJsonWhen()` → semua error API berbentuk JSON |
| 9 | Provider Sanctum tidak terdaftar bila `composer install --no-scripts` → *“Auth guard [sanctum] is not defined”* | `bootstrap/providers.php` mendaftarkan `SanctumServiceProvider` + `TinkerServiceProvider` secara eksplisit |
| 10 | Pencarian memakai operator `ilike` (khusus PostgreSQL) → error di SQLite/MySQL | Helper `likeOperator()` yang menyesuaikan driver database |
| 11 | Seeder gagal: `AuditLog::insert()` barisnya beda jumlah kolom | Semua baris disamakan kolomnya → `migrate --seed` sukses |
| 12 | Halaman `/` masih template bawaan Laravel dan menampilkan teks `@fonts` (direktif tak dikenal) | Diganti halaman status HealthHub: cek `/api/health`, daftar poliklinik & dokter live, demo AI triage, daftar endpoint, akun demo |
| 13 | Controller sisa (`BookingController`, `DoctorController`, `PolyclinicController`) duplikat/bodong | Dihapus — `/api/doctors` & `/api/polyclinics` ditangani `LookupController` |

---

## 1. Struktur File

```
├── composer.json                      # dependensi (laravel/framework ^12, sanctum ^4)
├── .env.example                       # konfigurasi app & database (default SQLite)
├── bootstrap/app.php                  # routing + alias middleware role + error JSON
├── bootstrap/providers.php            # provider app + Sanctum + Tinker
├── routes/api.php                     # seluruh route REST API
├── app/
│   ├── Models/*.php                   # 1 kelas = 1 file (User, Patient, Doctor, ... 42 model)
│   └── Http/
│       ├── Middleware/EnsureRole.php       # RBAC: role:doctor,nurse
│       ├── Middleware/ForceJsonResponse.php
│       └── Controllers/
│           ├── ApiController.php          # base: ok()/fail()/audit()/requireRole()/likeOperator()
│           ├── AuthController.php         # login, logout, me, register
│           ├── LookupController.php       # doctors, polyclinics, specs, services, schedules, patients, settings, articles
│           ├── AppointmentController.php  # appointments + queues (slot locking, reminder, broadcast text)
│           ├── AiTriageController.php     # triage klinis dengan emergency red flags
│           ├── ClinicalController.php     # EMR, vital signs, break-glass
│           ├── PharmacyLabController.php  # farmasi + laboratorium
│           └── FinanceAdminController.php # billing, beds/inpatients, notifikasi, audit, seed endpoint
├── database/
│   ├── migrations/                    # 4 migrasi domain + tabel cache & personal access token
│   └── seeders/DatabaseSeeder.php     # 11 user demo, 4 dokter, EMR, antrean hari ini, dsb.
└── resources/views/welcome.blade.php  # halaman status API di http://localhost:8000
```

## 2. Cara Menjalankan (localhost)

```bash
# 1) Dependensi PHP
composer install

# 2) Konfigurasi environment
cp .env.example .env
php artisan key:generate

# 3) Database SQLite (default, tanpa server DB)
touch database/database.sqlite          # Windows: new-item database/database.sqlite
#   → atau langsung `php artisan migrate` lalu jawab "yes" saat ditawari membuat file-nya

# 4) Migrasi + seed data demo lengkap
php artisan migrate --seed              # atau POST /api/seed (FinanceAdminController@seed)

# 5) Jalankan
php artisan serve                       # http://127.0.0.1:8000
```

Buka `http://127.0.0.1:8000` — halaman status akan menampilkan hasil cek `/api/health`,
daftar poliklinik/dokter dari database, dan form demo AI triage.

**Pakai PostgreSQL/MySQL?** Buka `.env`, ganti 5 baris `DB_*` (contoh sudah disediakan sebagai
komentar), lalu `php artisan migrate:fresh --seed`.

Opsional frontend asset (halaman status sudah tanpa build, jadi ini hanya perlu bila Anda
memakai `@vite`): `npm install && npm run build`.

## 3. Kredensial Demo (password semua: `password123`)

| Peran | Email |
|---|---|
| Super Admin | superadmin@healthhub.id |
| Admin RS | admin@healthhub.id |
| Dokter | doctor@healthhub.id |
| Perawat | nurse@healthhub.id |
| Farmasi | pharmacy@healthhub.id |
| Analis Lab | lab@healthhub.id |
| Kasir | cashier@healthhub.id |
| Pasien | patient@healthhub.id |

```bash
curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"doctor@healthhub.id","password":"password123"}'
# → { "success": true, "data": { "token": "...", "user": {...} } }
```
Gunakan `Authorization: Bearer <token>` untuk endpoint tulis. Semua respons memakai bentuk
`{success, message, data}`; error RBAC = 403, slot bentrok = 409.

## 4. Pengingat ≤ 2 Jam (background job)

Tambahkan pada `routes/console.php` (Laravel 11+) atau schedule di `bootstrap/app.php`:

```php
Schedule::call(fn () => app(\App\Http\Controllers\AppointmentController::class)->processDueReminders())
    ->everyMinute()->name('appointment-reminders-sub2h')->withoutOverlapping();
```

+ `php artisan queue:work` (driver `QUEUE_CONNECTION=database`).

## 5. Jika Halaman Tidak Muncul (checklist cepat)

1. `php -v` → butuh **PHP ≥ 8.3** dengan ekstensi `mbstring, openssl, pdo_sqlite` (SQLite) atau `pdo_pgsql`/`pdo_mysql`.
2. `composer install` sudah dijalankan? Cek folder `vendor/` ada.
3. `.env` sudah ada dan `APP_KEY` terisi? (`php artisan key:generate`, lalu `php artisan config:clear`).
4. Sudah `php artisan migrate --seed`? Tanpa itu tabel (dan halaman status) kosong/error.
5. Port 8000 dipakai proses lain? `php artisan serve --port=8080`.
6. Lihat pesan asli: `storage/logs/laravel.log`.
