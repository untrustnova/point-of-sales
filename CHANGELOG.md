# Changelog

Semua perubahan penting pada proyek POS Nova didokumentasikan di file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
dan proyek ini mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0-beta] — 2026-08-12

Rilis beta pertama POS Nova. Fondasi arsitektur, autentikasi, dan modul kasir dasar telah dibangun.

### Added

#### Backend & Arsitektur
- Inisialisasi proyek Laravel 13 dengan React Starter Kit (Inertia.js v3 + React 19)
- Konfigurasi PHP 8.5 dengan Pest 5 untuk testing
- Integrasi Spatie Permission untuk role-based access control (Owner, Manager, Kasir, Staff Gudang)
- Integrasi Laravel Fortify untuk autentikasi (login, register, 2FA TOTP, passkeys, password reset, email verification)
- Integrasi Laravel Reverb untuk WebSocket broadcasting
- Integrasi Laravel Scout untuk full-text search produk
- Integrasi Intervention Image untuk resize & compress gambar produk
- Integrasi Maatwebsite Excel untuk import/export data
- Integrasi Barryvdh DomPDF untuk cetak struk & laporan PDF
- Integrasi Laravel Wayfinder untuk typed route functions

#### Database & Seeder
- Migration untuk seluruh tabel inti: `users`, `roles`, `permissions`, `stores`, `categories`, `products`, `product_variants`, `units`, `stock_movements`, `transactions`, `transaction_items`, `payments`, `customers`, `suppliers`, `purchase_orders`, `cashier_shifts`, `expenses`, `settings`, `activity_logs`
- Seeder untuk 4 role predefined (Owner, Manager, Kasir, Staff Gudang) dengan permission granular
- `UserSeeder` dengan 4 akun demo (`owner@posnova.test`, `manager@posnova.test`, `kasir@posnova.test`, `gudang@posnova.test`) + 10 random factory users
- `StoreSeeder` untuk data toko default
- Factory untuk `User`, `Product`, `Category`, `Store`, dan model lainnya

#### Frontend — Halaman & Layout
- **Welcome Page**: Landing page modern dengan hero, feature badges, dan quick credential card
- **Login Page**: Redesign dengan 1-click demo account selector (auto-fill email/password), passkey support, dan 2FA
- **Dashboard Eksekutif**: KPI cards (omset bersih, transaksi, laba bersih), quick action toolbar, live transaction feed, top 5 produk terlaris, low stock alert center
- **POS Kasir**: UI kasir full-screen dengan pencarian produk cepat, filter kategori, grid produk responsif, keranjang belanja, qty controls, summary totals, payment modal (Tunai/QRIS/Kartu/Kredit), hold/park order
- **Shift Kasir**: Buka/tutup shift dengan modal kas awal, rekonsiliasi kas, riwayat shift
- **Katalog Produk**: Data table produk dengan search, filter, pagination
- **App Sidebar**: Navigasi terstruktur per modul (Operasional Kasir, Katalog & Inventori, Laporan & CRM, Manajemen Toko) dengan role-aware visibility

#### Frontend — Komponen & UI System
- Integrasi shadcn/ui components (Button, Card, Badge, Dialog, Select, Tooltip, dll.)
- Seluruh halaman menggunakan shadcn CSS variable theme tokens (`bg-background`, `bg-card`, `bg-muted`, `border-border`, `text-foreground`, `text-muted-foreground`, `bg-primary`, `text-primary-foreground`) untuk konsistensi light/dark mode
- Desain mengikuti prinsip Hallmark anti-AI-slop: tipografi presisi, layout asimetris, micro-interactions yang bermakna
- Helper functions: `formatRupiah()` dan `formatDate()` di `lib/utils.ts`
- POS Layout terpisah dengan top navbar, drawer menu, dan keyboard shortcut support

#### DevOps & CI/CD
- Multi-stage `Dockerfile` (Bun build → PHP-FPM Alpine production)
- `docker-compose.yml` dengan 5 services: app, db (MySQL), redis, queue worker, reverb (WebSocket)
- GitHub Actions CI pipeline: lint (Pint) + test (Pest) pada push/PR
- GitHub Actions Release pipeline: build & push Docker image ke GHCR pada tag push
- `.dockerignore` untuk optimasi build context

#### Testing
- 117 test cases passing (Pest 5)
- Feature tests untuk autentikasi, role/permission, user seeder, dashboard, POS, shift, produk
- Test coverage untuk Form Request validation, Policy authorization, dan Inertia response

### Technical Notes
- Strategi query: Eloquent ORM untuk CRUD standar, `DB::table` / raw query untuk laporan agregat
- Seluruh transaksi keuangan/stok dibungkus `DB::transaction()`
- Database schema sudah menyertakan `store_id` sejak awal untuk kesiapan multi-store di masa depan
- Frontend build menggunakan Bun (bukan npm/yarn)
- Arsitektur Controller → Service → Model untuk separation of concerns

[0.1.0-beta]: https://github.com/untrustnova/point-of-sales/releases/tag/v0.1.0-beta
