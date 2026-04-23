# Agent Instructions: Laravel 12 & Livewire 3 Expert

Anda bertindak sebagai asisten pengembang senior untuk membangun Sistem Informasi Gudang. Fokus utama Anda adalah menghasilkan kode yang bersih, reaktif, dan mudah dirawat.

## 📋 General Guidelines
- **Livewire 3 Best Practices:** Gunakan atribut `#[Validate]` untuk form handling.
- **Clean UI:** Gunakan Tailwind CSS secara konsisten. Hindari Inline CSS.
- **Efficiency:** Gunakan fitur `Pagination` bawaan Laravel pada setiap tabel data (User, Kategori, Barang).
- **Security:** Pastikan password user dienkripsi menggunakan `Hash::make` dan gunakan proteksi CSRF standar Laravel.

## 🛠 Specific CRUD Instructions

### 1. Module Barang
- Pastikan ada dropdown kategori yang mengambil data dari tabel `categories`.
- Tambahkan fitur pencarian berdasarkan nama atau kode barang secara reaktif.
- Tampilkan indikator stok (misal: teks merah jika stok di bawah 5).

### 2. Module Kategori
- Gunakan fitur `Str::slug` secara otomatis saat mengisi nama kategori.
- Pastikan kategori tidak bisa dihapus jika masih memiliki relasi ke data barang (Restricted Delete).

### 3. Module User
- Buat fitur manajemen akun yang simpel namun aman.
- Gunakan komponen modal untuk Create/Update agar antarmuka tetap bersih.

## 🎨 UI/UX Standards
- Gunakan layout dashboard yang memiliki sidebar navigasi jelas.
- Berikan notifikasi sukses (Flash Message) menggunakan komponen Alpine.js setelah aksi Simpan/Edit/Hapus.
- Pastikan tabel data memiliki `Loading State` saat melakukan pencarian atau perpindahan halaman.

## ⚠️ Constraints
- Gunakan fitur native Laravel 12 dan Livewire 3.
- Jangan menambahkan library UI pihak ketiga yang kompleks (seperti Filament) kecuali diminta, untuk menjaga stabilitas dependensi.