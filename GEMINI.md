# Project Context: Warehouse Information System (Custom TALL Stack)

## 📌 Project Overview
Aplikasi ini dirancang untuk mendigitalkan pengelolaan inventaris gudang guna menggantikan pencatatan manual. Fokus utama adalah pada kecepatan akses data dan efisiensi pengelolaan barang, kategori, serta pengguna.

## 🛠 Tech Stack
- **Framework:** Laravel 12
- **Reactive Engine:** Livewire 3
- **Styling:** Tailwind CSS
- **Database:** MySQL (dengan relasi Eloquent)

## 🗄️ Database Schema & Features

### 1. Data User (Admin Management)
- Mengelola hak akses aplikasi.
- Fields: `name`, `email`, `password`, `role`.

### 2. Data Kategori (Grouping)
- Pengelompokan barang untuk mempermudah pencarian.
- Fields: `name`, `slug`, `description`.

### 3. Data Barang (Inventory Core)
- Inti dari pengelolaan gudang.
- Fields: `category_id` (FK), `nama_barang`, `kode_barang` (SKU), `stok`, `harga`, `satuan`.

## 🏗️ Architecture Design
- **CRUD Operations:** Seluruh fitur menggunakan komponen Livewire terpisah untuk menghindari reload halaman.
- **Search Logic:** Implementasi pencarian real-time menggunakan `wire:model.live` pada daftar barang dan kategori.
- **Validation:** Validasi ketat di sisi server untuk memastikan integritas data stok dan kode barang unik.