# POS System — Requirement & Architecture Document

> Dokumen ini adalah **single source of truth** untuk pengembangan aplikasi Point of Sales (POS) berbasis Laravel + Inertia + React. Ditujukan untuk dibaca oleh developer manusia **maupun AI coding agent**. Setiap agent yang mengerjakan task dari dokumen ini **wajib** membaca `AGENTS.md`, `package.json`, dan `composer.json` di root project terlebih dahulu sebelum menulis kode apa pun.

---

## 1. Ringkasan Proyek

| | |
|---|---|
| **Nama kerja (working title)** | POS App (nama final menyusul) |
| **Target pengguna** | Pedagang/pengusaha kecil–menengah (toko retail, F&B, minimarket, dsb) |
| **Filosofi produk** | User-friendly, cepat dioperasikan oleh kasir non-teknis, mudah dimanage oleh owner |
| **Skop saat ini** | **Single-store / single-tenant** — tapi struktur database & arsitektur harus tetap *future-proof* untuk multi-cabang di fase berikutnya |
| **Platform** | Web app responsif (desktop untuk back-office, tablet untuk kasir, mobile untuk owner cek laporan) |

### Prinsip Desain Utama
- **Cepat** — interaksi kasir tidak boleh terasa lambat (optimistic UI, debounce search, index database yang tepat).
- **User-friendly** — pemilik toko awam teknologi harus bisa pakai tanpa training panjang.
- **Modern & profesional** — bukan tampilan "AI slop" generik (hero-3 fitur-CTA template). Gunakan skill **Hallmark** untuk memastikan hasil desain punya karakter, bukan template pasaran.
- **Robust** — validasi ketat di setiap transaksi keuangan/stok, karena ini menyangkut uang nyata pedagang.

---

## 2. Tech Stack

### Backend
- **Laravel 13.x** (rilis Maret 2026, minimum PHP 8.3 — kita pakai **PHP 8.5**)
- **Inertia.js** sebagai jembatan Laravel ↔ React (bukan REST API terpisah untuk v1 — non-API, SSR-less Inertia flow)
- **React** via Laravel React Starter Kit resmi
- **Laravel Reverb** — WebSocket server untuk fitur realtime (notifikasi stok, live dashboard, live shift kasir)
- **Spatie Laravel-Permission** — role & permission (Owner, Manager, Kasir, Gudang, dst.)
- **Intervention/Image** — resize, compress, crop, watermark gambar produk
- **Laravel Excel (maatwebsite/excel)** — import/export Excel (produk, laporan, stok)
- **Laravel PDF** (`spatie/laravel-pdf` atau `barryvdh/laravel-dompdf`) — cetak struk, invoice, laporan PDF
- **Laravel Sanctum** — jika suatu saat butuh token auth (mobile app kasir terpisah, dsb — opsional fase depan)

### Frontend
- **TailwindCSS** + **shadcn/ui** + **Radix UI** — komponen aksesibel & konsisten
- **Framer Motion** — animasi transisi halaman & micro-interaction
- **Lenis** — smooth scroll (halaman laporan panjang, katalog produk)
- **Sonner** — toast notification (feedback aksi: sukses/gagal/warning)
- **Chart.js** atau **shadcn charts (Recharts)** — grafik dashboard & laporan
- **Hallmark** (`bunx --bun skills add nutlope/hallmark`) — design skill anti-AI-slop, dipakai setiap kali membangun/mendesain ulang UI baru agar tidak jatuh ke template generik

### Tooling & Infra
- **PHP 8.5**
- **Bun** sebagai package manager & runtime frontend (bukan npm/yarn)
- **Docker & Docker Compose** untuk environment dev maupun production-ready
- **MySQL/PostgreSQL** (pilih salah satu — rekomendasi PostgreSQL untuk query analitik yang lebih berat, atau MySQL jika tim lebih familiar)
- **Redis** — cache, queue, session, dan broadcasting driver pendukung Reverb
- **Laravel Horizon** (opsional) — monitoring queue jika volume transaksi tinggi

### Strategi Query
- **Eloquent ORM** → default untuk seluruh operasi CRUD standar (create/update/delete, relasi sederhana).
- **`DB::table` / raw query** → wajib dipakai untuk:
  - Laporan agregat lintas banyak tabel (laporan penjualan per periode, laba rugi, produk terlaris).
  - Query dashboard yang butuh performa tinggi & dieksekusi berulang (polling/realtime).
  - Kasus di mana Eloquent berpotensi N+1 atau overhead hydration model yang tidak perlu.
- Query berat sebaiknya diletakkan di **Service layer** (misal `ReportService`, `DashboardService`), bukan langsung di Controller.

---

## 3. Alur Arsitektur (Development Flow)

```
View (React + Inertia)
   ↓
Route (routes/web.php)
   ↓
Controller (non-API, return Inertia::render)
   ↓
Service (business logic, query berat, transaksi DB)
   ↓
Model (Eloquent) ── + Policy, Middleware, Exception, Provider, Form Request, Resource
```

### Aturan Wajib untuk Developer/AI Agent
1. **Selalu cek `AGENTS.md`, `package.json`, dan `composer.json`** sebelum memulai task apa pun — untuk memastikan konvensi, dependency, dan script yang sudah ada tidak dilanggar/diduplikasi.
2. **Controller tetap tipis** — tidak boleh berisi query kompleks atau business logic; delegasikan ke Service.
3. **Semua validasi input** lewat **Form Request** class (`app/Http/Requests`), bukan validasi manual di controller.
4. **Response data ke frontend** lewat **API Resource** (`app/Http/Resources`) agar payload Inertia konsisten & tidak bocor field sensitif.
5. **Otorisasi** lewat **Policy** (`app/Policies`) yang terintegrasi dengan Spatie Permission, dicek di Controller via `$this->authorize()` atau middleware `can:`.
6. **Middleware** custom untuk hal seperti: cek toko aktif, cek shift kasir sedang terbuka, dsb.
7. **Custom Exception** untuk domain error (misal `InsufficientStockException`, `ShiftAlreadyClosedException`) agar pesan error konsisten & bisa ditangani rapi di frontend (toast Sonner).
8. **Service Provider** custom untuk binding interface↔implementation (misal `PaymentGatewayInterface`, `ReceiptPrinterInterface`) agar mudah diganti/di-mock saat testing.
9. Transaksi yang menyentuh **stok & uang** wajib dibungkus `DB::transaction()`.

### Struktur Folder (usulan)
```
app/
  Http/
    Controllers/
    Requests/
    Resources/
    Middleware/
  Services/
  Models/
  Policies/
  Providers/
  Exceptions/
  Events/           # untuk broadcasting via Reverb
  Listeners/
  Jobs/             # export excel/pdf besar, notifikasi async
resources/
  js/
    Pages/          # halaman Inertia
    Components/     # shadcn + komponen custom
    Layouts/
    hooks/
    lib/
routes/
  web.php
docker/
  php/
  nginx/
  reverb/
```

---

## 4. Modul & Fitur Utama

### 4.1 Autentikasi & Manajemen Pengguna
- [ ] Login / logout
- [ ] Role & permission berbasis Spatie: **Owner**, **Manager/Admin**, **Kasir**, **Staff Gudang** (role bisa dikustomisasi owner)
- [ ] Manajemen user (invite staff, aktif/nonaktifkan akun — bukan self-register publik)
- [ ] Profile & ganti password
- [ ] Activity log / audit trail (siapa mengubah apa, kapan)

### 4.2 Dashboard & Analytics
- [ ] Ringkasan penjualan hari ini / minggu / bulan (realtime via Reverb)
- [ ] Grafik pendapatan & tren penjualan (Chart.js/Recharts)
- [ ] Produk terlaris & produk paling jarang terjual
- [ ] Jam/hari tersibuk (heatmap sederhana)
- [ ] Notifikasi live: stok menipis, transaksi besar masuk, shift dibuka/ditutup

### 4.3 Manajemen Produk
- [ ] CRUD produk, kategori, sub-kategori, brand
- [ ] Varian produk (ukuran, warna, dll)
- [ ] Satuan & konversi unit (pcs ↔ box ↔ dus)
- [ ] SKU & barcode (generate otomatis + support scan barcode fisik)
- [ ] Multi harga: harga beli, harga jual, harga grosir/reseller
- [ ] Upload & kelola gambar produk (Intervention/Image: resize, compress otomatis)
- [ ] Produk bundling/paket

### 4.4 Manajemen Stok & Inventori
- [ ] Stok masuk & keluar manual
- [ ] Stok opname / penyesuaian stok dengan catatan alasan
- [ ] Riwayat pergerakan stok (stock ledger, siapa & kapan)
- [ ] Notifikasi realtime stok menipis (threshold per produk)
- [ ] Tracking kadaluarsa produk (opsional — penting untuk toko makanan/farmasi)
- [ ] Struktur siap multi-gudang (`warehouse_id`) meski sekarang 1 toko

### 4.5 Point of Sale (Halaman Kasir)
- [ ] UI kasir cepat & touch-friendly, optimized untuk tablet
- [ ] Pencarian produk cepat (nama, SKU, scan barcode)
- [ ] Keranjang belanja + diskon per item / per transaksi
- [ ] Multi metode pembayaran: tunai, kartu, e-wallet, **QRIS**, split payment
- [ ] Hitung kembalian otomatis
- [ ] Tahan/parkir transaksi (hold order) untuk dilanjutkan nanti
- [ ] Cetak struk (support thermal printer 58mm/80mm)
- [ ] Void/batalkan transaksi (dengan approval role tertentu)
- [ ] Retur & refund penjualan
- [ ] **Shift kasir**: buka shift (modal awal kas), tutup shift (rekonsiliasi kas fisik vs sistem)

### 4.6 Manajemen Pelanggan (CRM Ringan)
- [ ] Database pelanggan (nama, kontak, alamat)
- [ ] Riwayat pembelian per pelanggan
- [ ] Program poin/loyalti (fase lanjutan)
- [ ] Piutang pelanggan jika toko memberi kredit/utang

### 4.7 Manajemen Supplier & Pembelian
- [ ] Database supplier
- [ ] Purchase Order (PO) ke supplier
- [ ] Penerimaan barang → otomatis update stok
- [ ] Hutang ke supplier (accounts payable)
- [ ] Retur pembelian ke supplier

### 4.8 Laporan & Export/Import
- [ ] Laporan penjualan (harian/mingguan/bulanan/custom range)
- [ ] Laporan laba rugi sederhana
- [ ] Laporan stok & pergerakan stok
- [ ] Laporan produk terlaris/terlambat
- [ ] Export ke Excel (Laravel Excel)
- [ ] Export ke PDF (invoice, struk, laporan)
- [ ] Import massal data produk dari Excel/CSV

### 4.9 Keuangan Sederhana
- [ ] Pencatatan pengeluaran operasional (expenses: listrik, sewa, gaji, dll)
- [ ] Ringkasan kas masuk/keluar harian
- [ ] Rekonsiliasi kas dari hasil tutup shift kasir

### 4.10 Pengaturan (Settings)
- [ ] Info toko: nama, alamat, logo, NPWP
- [ ] Pengaturan pajak (PPN, service charge jika F&B)
- [ ] Pengaturan mata uang & format angka
- [ ] Kustomisasi struk (header/footer, logo, catatan promosi)
- [ ] Pengaturan printer thermal
- [ ] Pengaturan role & permission granular
- [ ] Backup/restore database (opsional, minimal manual export)

### 4.11 Realtime (Laravel Reverb)
- [ ] Broadcast event stok menipis ke dashboard owner
- [ ] Broadcast transaksi baru ke dashboard (live sales feed)
- [ ] Broadcast status shift kasir (dibuka/ditutup) ke manager

### 4.12 Kesiapan Multi-Store (Fase Depan — bukan sekarang)
- [ ] Skema database menyertakan `store_id` sejak awal (default 1 store), agar migrasi ke multi-cabang tidak butuh rewrite besar

---

## 5. UI/UX Requirements

- Desain **profesional & modern**, hindari pola template generik — gunakan **Hallmark** skill setiap kali membangun halaman baru:
  ```
  bunx --bun skills add nutlope/hallmark
  ```
  Jalankan mode `build` untuk halaman baru, `audit` untuk cek halaman existing, `redesign` bila perlu merombak tampilan tanpa mengubah struktur data/route.
- **Responsif**: desktop (back-office/laporan), tablet (kasir — prioritas utama kecepatan sentuh), mobile (owner cek ringkasan).
- **Animasi halus** dengan Framer Motion untuk transisi antar halaman & micro-interaction (bukan animasi berlebihan yang memperlambat kasir).
- **Lenis** untuk smooth scroll di halaman panjang (laporan, katalog produk).
- **Sonner** untuk semua feedback aksi (sukses, gagal, warning) — konsisten di seluruh aplikasi.
- Dark mode (disarankan, tidak wajib di MVP).
- Navigasi kasir harus bisa dioperasikan cepat dengan keyboard shortcut (tanpa mouse) untuk mempercepat transaksi.

---

## 6. Pertimbangan Desain Database

Tabel inti (indikatif, disesuaikan saat migration):

`users`, `roles`, `permissions`, `stores`, `categories`, `products`, `product_variants`, `units`, `stock_movements`, `warehouses`, `transactions`, `transaction_items`, `payments`, `customers`, `suppliers`, `purchase_orders`, `purchase_order_items`, `cashier_shifts`, `expenses`, `settings`, `activity_logs`

Catatan:
- Soft delete untuk data master penting (`products`, `customers`, `suppliers`) — jangan hard delete data yang punya riwayat transaksi.
- Index pada kolom yang sering dipakai filter/join: `sku`, `barcode`, tanggal transaksi, `store_id`, `product_id`.
- Gunakan `DB::table`/raw query untuk laporan agregat lintas tabel besar (lihat bagian Strategi Query di atas).
- Semua tabel transaksional sebaiknya sudah menyertakan `store_id` walau saat ini hanya 1 baris di tabel `stores`.

---

## 7. Docker & Environment

Rencana `docker-compose.yml` services:

| Service | Fungsi |
|---|---|
| `app` | PHP-FPM 8.5 (Laravel) |
| `nginx` | Web server / reverse proxy |
| `db` | MySQL atau PostgreSQL |
| `redis` | Cache, queue, session |
| `reverb` | WebSocket server (Laravel Reverb) |
| `queue` | Worker untuk job async (export besar, notifikasi) |
| `bun` (build stage) | Build asset frontend, multi-stage sehingga image production tidak membawa dev dependencies |

Catatan tambahan:
- Dockerfile disarankan **multi-stage**: stage build (Bun install + build) → stage production (PHP-FPM + hasil build asset saja).
- Sediakan `.env.example` lengkap dengan seluruh variabel (DB, Redis, Reverb, mail, dsb).
- Sediakan `Makefile` atau script bash sederhana untuk perintah umum (`up`, `migrate`, `seed`, `fresh`).

---

## 8. Roadmap Pengembangan (Fase)

- **Fase 1 — MVP**: Auth & role dasar, manajemen produk, kasir dasar (transaksi + cetak struk), laporan penjualan sederhana.
- **Fase 2 — Inventori & Purchasing**: Stok opname, purchase order, supplier, hutang supplier.
- **Fase 3 — CRM & Laporan Lanjutan**: Manajemen pelanggan, loyalti/poin, export/import Excel & PDF lengkap.
- **Fase 4 — Realtime & Operasional**: Notifikasi Reverb, shift kasir & rekonsiliasi kas, integrasi printer thermal fisik.
- **Fase 5 — Polish & Kesiapan Scale**: Dark mode, aksesibilitas, audit performa query, kesiapan struktur multi-store, testing menyeluruh.

---

## 9. Testing & QA

- [ ] Unit test untuk seluruh Service (business logic inti: perhitungan stok, perhitungan kembalian, validasi shift).
- [ ] Feature test untuk Controller (memastikan response Inertia & otorisasi Policy berjalan benar).
- [ ] Manual QA checklist khusus untuk alur kasir (karena menyentuh uang & stok nyata) sebelum tiap rilis ke production.
- [ ] Uji beban ringan pada query laporan (`DB::table`) untuk memastikan tidak lambat saat data transaksi sudah besar.

---

## 10. Catatan Khusus untuk AI Coding Agent

1. **Wajib** baca `AGENTS.md`, `package.json`, dan `composer.json` di root proyek sebelum mengerjakan task apa pun dari daftar di atas.
2. Ikuti alur wajib: **View → Route → Controller → Service → Model**, lengkap dengan Policy/Middleware/Exception/Provider/Request/Resource sesuai kebutuhan tiap fitur.
3. Gunakan **Eloquent** untuk operasi CRUD biasa, **`DB::table`/raw query** untuk laporan & query berat — jangan campur aduk tanpa alasan performa yang jelas.
4. Setiap membangun atau mengubah tampilan UI baru, jalankan skill **Hallmark** (`bunx --bun skills add nutlope/hallmark`) agar hasil desain tidak generik.
5. Package manager frontend **wajib Bun**, bukan npm/yarn — pastikan tidak ada `package-lock.json` yang tercipta tidak sengaja.
6. Semua fitur yang menyentuh stok/uang wajib dibungkus `DB::transaction()` dan diuji dengan skenario gagal (rollback).
7. Checklist fitur di atas ( `- [ ]` ) dapat dicentang (`- [x]`) langsung di file ini seiring progres implementasi, sehingga dokumen ini juga berfungsi sebagai **progress tracker**.

---

*Dokumen ini hidup (living document) — update sesuai keputusan tim saat development berjalan.*

---

Additional:

The `tooltip` component has been added. Remember to wrap your app with the `TooltipProvider` component.

```tsx title="app/layout.tsx"
import { TooltipProvider } from "@/components/ui/tooltip"

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>
        <TooltipProvider>{children}</TooltipProvider>
      </body>
    </html>
  )
}
```

The `tooltip` component has been added. Remember to wrap your app with the `TooltipProvider` component.

```tsx title="app/layout.tsx"
import { TooltipProvider } from "@/components/ui/tooltip"

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>
        <TooltipProvider>{children}</TooltipProvider>
      </body>
    </html>
  )
}
```

---
