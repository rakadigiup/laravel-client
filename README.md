# Sabila - Warehouse Information System 📦

Aplikasi Pengelolaan Inventaris Gudang berbasis web yang dirancang untuk mendigitalkan pencatatan barang, kategori, dan manajemen pengguna secara efisien dan responsif.

## 🛠️ Tech Stack

- **Framework:** [Laravel 12](https://laravel.com)
- **Reactive Engine:** [Livewire 3 (Volt)](https://livewire.laravel.com)
- **UI Components:** [Flux UI](https://fluxui.dev)
- **Styling:** [Tailwind CSS v4](https://tailwindcss.com)
- **Database:** MySQL / SQLite (Eloquent ORM)

## ✨ Fitur Utama

- **Dashboard Analytics:** Visualisasi jumlah kategori, total barang, dan total user secara real-time.
- **Low Stock Alert:** Indikator otomatis (warna merah) untuk barang dengan stok di bawah 5 unit.
- **Manajemen Kategori:** CRUD kategori dengan fitur auto-slug.
- **Manajemen Barang:** Inventaris lengkap dengan kode SKU unik, kategori, harga, dan satuan.
- **Keamanan Tingkat Lanjut:** 
    - Middleware Admin untuk membatasi akses pengelolaan user.
    - Restricted Deletion: Kategori tidak dapat dihapus jika masih memiliki relasi data barang.
- **Pencarian Real-time:** Fitur search debounced pada semua modul tanpa reload halaman.

## 🚀 Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan project di lingkungan lokal:

1. **Clone Repository**
   ```bash
   git clone https://github.com/rakadigiup/laravel-client.git
   cd laravel-client
   ```

2. **Instal Dependensi PHP**
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend**
   ```bash
   npm install
   npm run build
   ```

4. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env` dan sesuaikan pengaturan database Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Migrasi dan Seeding**
   Jalankan migrasi untuk membuat tabel dan mengisi data awal (admin & sampel barang).
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Jalankan Server**
   ```bash
   php artisan serve
   ```

## 🔑 Akun Uji Coba (Default)

Setelah menjalankan seeder, Anda dapat login menggunakan akun berikut:

| Peran (Role) | Email | Password |
| --- | --- | --- |
| **Administrator** | `admin@gudang.com` | `password` |
| **Operator/User** | `user@gudang.com` | `password` |

## 📁 Struktur Penting

- `resources/views/livewire/`: Berisi komponen Volt untuk logika bisnis CRUD.
- `app/Http/Middleware/AdminMiddleware.php`: Middleware untuk proteksi akses admin.
- `database/migrations/`: Skema database dengan relasi FK yang ketat.

---
Dikembangkan dengan ❤️ untuk efisiensi pengelolaan gudang.
