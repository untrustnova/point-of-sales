<div align="center">

# POS Nova

**Sistem Kasir & Manajemen Toko Modern**

Aplikasi Point of Sale (POS) open-source berbasis Laravel 13 + React + Inertia.js — dirancang untuk pedagang kecil–menengah yang membutuhkan sistem kasir cepat, handal, dan mudah digunakan.

[![CI](https://github.com/untrustnova/point-of-sales/actions/workflows/ci.yml/badge.svg)](https://github.com/untrustnova/point-of-sales/actions/workflows/ci.yml)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-19-61DAFB?logo=react&logoColor=black)](https://react.dev)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

</div>

---

## Fitur Utama

| Modul | Deskripsi |
|---|---|
| **POS Kasir** | UI kasir cepat & touch-friendly, pencarian produk instan, multi metode pembayaran (Tunai, QRIS, Kartu), hold/park order, hitung kembalian otomatis |
| **Shift Kasir** | Buka/tutup shift dengan modal kas awal, rekonsiliasi saldo kas fisik vs sistem |
| **Katalog Produk** | CRUD produk dengan SKU, barcode, multi-harga (beli/jual/grosir), kategori, varian, upload gambar |
| **Inventori & Stok** | Stok masuk/keluar, opname, riwayat pergerakan (stock ledger), notifikasi stok menipis |
| **Dashboard Eksekutif** | KPI real-time (omset, laba bersih, transaksi hari ini), produk terlaris, grafik penjualan |
| **Laporan & Export** | Laporan penjualan per periode, laba rugi, export Excel & PDF |
| **Manajemen Pelanggan (CRM)** | Database pelanggan, riwayat pembelian, piutang |
| **Purchase Order** | PO ke supplier, penerimaan barang otomatis update stok, hutang supplier |
| **Manajemen Staf** | Role-based access control (Owner, Manager, Kasir, Staff Gudang) via Spatie Permission |
| **Realtime** | Notifikasi WebSocket via Laravel Reverb — stok menipis, transaksi baru, status shift |

## Tech Stack

### Backend
- **Laravel 13** — PHP 8.5
- **Inertia.js v3** — SPA tanpa complexity REST API terpisah
- **Spatie Permission** — Role & permission granular
- **Laravel Reverb** — WebSocket server native Laravel
- **Laravel Scout** — Full-text search produk
- **Intervention Image** — Resize & compress gambar produk
- **Maatwebsite Excel** — Import/export data Excel
- **Barryvdh DomPDF** — Cetak struk, invoice, laporan PDF

### Frontend
- **React 19** + **TypeScript**
- **TailwindCSS v4** + **shadcn/ui** + **Radix UI**
- **Framer Motion** — Animasi transisi halaman
- **Lucide React** — Icon library konsisten
- **Sonner** — Toast notification
- **Lenis** — Smooth scroll

### Tooling & Infrastruktur
- **Bun** — Package manager & runtime frontend
- **Docker & Docker Compose** — Development & production environment
- **Redis** — Cache, queue, session
- **MySQL / PostgreSQL** — Database relasional
- **GitHub Actions** — CI/CD pipeline otomatis
- **Pest** — Testing framework PHP
- **Pint** — Code style formatter

## Quick Start

### Prasyarat

- PHP 8.5+
- Composer 2.x
- Bun 1.x (`curl -fsSL https://bun.sh/install | bash`)
- MySQL 8.0+ atau PostgreSQL 15+
- Redis 7+

### Instalasi Manual

```bash
# Clone repository
git clone https://github.com/untrustnova/point-of-sales.git
cd point-of-sales

# Install dependencies
composer install
bun install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Build frontend assets
bun run build

# Start development server
composer run dev
```

### Docker (Recommended)

```bash
# Clone & start semua service
git clone https://github.com/untrustnova/point-of-sales.git
cd point-of-sales
cp .env.example .env

# Jalankan dengan Docker Compose
docker compose up -d

# Setup database di dalam container
docker compose exec app php artisan migrate --seed

# Akses di http://localhost:8000
```

## Demo Accounts

Setelah menjalankan seeder (`php artisan db:seed`), tersedia akun demo berikut:

| Role | Email | Password |
|---|---|---|
| Owner | `owner@posnova.test` | `password` |
| Manager | `manager@posnova.test` | `password` |
| Kasir | `kasir@posnova.test` | `password` |
| Staff Gudang | `gudang@posnova.test` | `password` |

## Struktur Proyek

```
app/
  Http/
    Controllers/       # Thin controllers, delegasi ke Service
    Requests/          # Form Request validation
    Resources/         # API Resource (Inertia payload)
    Middleware/
  Services/            # Business logic & query berat
  Models/              # Eloquent models
  Policies/            # Authorization policies
  Events/              # Broadcasting events (Reverb)
  Jobs/                # Async jobs (export, notifikasi)
resources/
  js/
    pages/             # Halaman Inertia (React)
    components/        # shadcn/ui + komponen custom
    layouts/           # App layout & POS layout
    hooks/             # Custom React hooks
    lib/               # Utilities (formatRupiah, formatDate)
docker/                # Docker config files
.github/workflows/     # CI/CD pipelines
```

## Arsitektur Alur Data

```
React (Inertia Page)
    ↓
Route (routes/web.php)
    ↓
Controller (return Inertia::render)
    ↓
Service (business logic, DB::transaction)
    ↓
Model (Eloquent) + Policy + FormRequest + Resource
```

## Testing

```bash
# Run semua test
php artisan test --compact

# Run test spesifik
php artisan test --compact --filter=ShiftTest

# Run dengan format verbose
php artisan test

# Lint check PHP
vendor/bin/pint --test
```

## Roadmap

- [x] **Fase 1 — MVP**: Auth, role dasar, manajemen produk, kasir dasar, dashboard
- [ ] **Fase 2 — Inventori & Purchasing**: Stok opname, purchase order, supplier
- [ ] **Fase 3 — CRM & Laporan Lanjutan**: Manajemen pelanggan, loyalti, export Excel/PDF
- [ ] **Fase 4 — Realtime & Operasional**: Notifikasi Reverb, integrasi printer thermal
- [ ] **Fase 5 — Polish & Scale**: Dark mode, aksesibilitas, audit performa, multi-store

## Kontribusi

Kontribusi sangat diterima! Silakan buka issue atau pull request.

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/fitur-baru`)
3. Commit perubahan (`git commit -m 'feat: tambah fitur baru'`)
4. Push ke branch (`git push origin feature/fitur-baru`)
5. Buka Pull Request

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

<div align="center">

**POS Nova** — Dibuat dengan Laravel, React, dan secangkir kopi.

</div>
