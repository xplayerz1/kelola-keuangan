# HMSI Finance - Sistem Pengelolaan Keuangan

Aplikasi web untuk mengelola keuangan organisasi dengan fitur lengkap termasuk manajemen transaksi, saldo, laporan, dan multi-user role.

## 📋 Fitur Utama

-   **Manajemen Transaksi** - Catat pemasukan dan pengeluaran
-   **Manajemen Kategori** - Kelola kategori dengan integrasi KBBI API
-   **Manajemen Saldo** - Tracking saldo otomatis dengan Chart.js
-   **Laporan** - Generate dan export laporan ke PDF (DomPDF)
-   **Multi-User Role** - Admin, Bendahara, Viewer
-   **API Integration** - KBBI, Exchange Rate, Wilayah Indonesia

## 🔧 Requirements

-   PHP 8.2+
-   Composer
-   MySQL 8.0+
-   Node.js & NPM (optional, untuk asset compilation)
-   Git

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/xplayerz1/kelola-keuangan.git
cd kelola-keuangan
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Setup

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan pengaturan database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hmsi_finance
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Buat Database

Buat database baru di MySQL dengan nama sesuai `DB_DATABASE` di file `.env`:

```sql
CREATE DATABASE hmsi_finance;
```

### 6. Jalankan Migrasi & Seeder

```bash
# Jalankan migrasi untuk membuat struktur tabel
php artisan migrate

# Jalankan seeder untuk data awal (roles & admin user)
php artisan db:seed
```

### 7. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser dan akses: `http://127.0.0.1:8000`

## 🔐 Login Default

| Role  | Email            | Password    |
| ----- | ---------------- | ----------- |
| Admin | admin@hmsi.or.id | password123 |

> **Note:** User baru yang register akan mendapat role Viewer (read-only). Admin dapat mengubah role via menu Admin → Users.

## 📁 Struktur Project

```
kelola-keuangan/
├── app/
│   ├── Http/Controllers/    # Controllers
│   ├── Models/              # Eloquent Models
│   └── Observers/           # Transaction Observer
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/views/         # Blade templates
├── routes/web.php           # Route definitions
└── public/                  # Public assets
```

## 🌐 API Integration (5 API)

| No  | API                   | Kegunaan                              |
| --- | --------------------- | ------------------------------------- |
| 1   | KBBI API              | Saran kata untuk nama kategori        |
| 2   | Exchange Rate API     | Kurs mata uang USD ke IDR             |
| 3   | Wilayah Indonesia API | Dropdown provinsi/kabupaten/kecamatan |
| 4   | Chart.js API          | Visualisasi grafik saldo              |
| 5   | World Time API        | Timestamp akurat untuk laporan        |

## 🛠 Tech Stack

-   **Backend:** Laravel 11
-   **Frontend:** Bootstrap 5, Font Awesome 6
-   **Database:** MySQL
-   **PDF Export:** DomPDF
-   **Excel Export:** Maatwebsite Excel
-   **Charts:** Chart.js

## 📝 Troubleshooting

### Error: SQLSTATE[42S02] Table doesn't exist

```bash
php artisan migrate:fresh --seed
```

### Error: The key is too long

Tambahkan di `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```

### Error: Storage permission

```bash
chmod -R 775 storage bootstrap/cache
```

### Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📄 License

MIT License
