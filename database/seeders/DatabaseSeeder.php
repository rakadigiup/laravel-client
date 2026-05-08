<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Admin Gudang',
            'email' => 'admin@gudang.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // Regular User (for testing role restrictions)
        User::factory()->create([
            'name' => 'User Biasa',
            'email' => 'user@gudang.com',
            'password' => 'password',
            'role' => 'user',
        ]);

        // Categories
        $elektronik = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'description' => 'Peralatan elektronik dan gadget',
        ]);

        $furniture = Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'description' => 'Meja, kursi, dan perlengkapan kantor',
        ]);

        $atk = Category::create([
            'name' => 'Alat Tulis Kantor',
            'slug' => 'alat-tulis-kantor',
            'description' => 'Perlengkapan tulis menulis untuk kantor',
        ]);

        // Items
        Item::create([
            'category_id' => $elektronik->id,
            'nama_barang' => 'Laptop ASUS ROG',
            'kode_barang' => 'LPT-001',
            'stok' => 5,
            'harga' => 15000000,
            'satuan' => 'Unit',
        ]);

        Item::create([
            'category_id' => $elektronik->id,
            'nama_barang' => 'Monitor LG 27 inch',
            'kode_barang' => 'MNT-001',
            'stok' => 3,
            'harga' => 3500000,
            'satuan' => 'Unit',
        ]);

        Item::create([
            'category_id' => $furniture->id,
            'nama_barang' => 'Kursi Gaming Secretlab',
            'kode_barang' => 'KRS-001',
            'stok' => 10,
            'harga' => 5000000,
            'satuan' => 'Unit',
        ]);

        Item::create([
            'category_id' => $furniture->id,
            'nama_barang' => 'Meja Kerja Standing',
            'kode_barang' => 'MJA-001',
            'stok' => 2,
            'harga' => 2500000,
            'satuan' => 'Unit',
        ]);

        Item::create([
            'category_id' => $atk->id,
            'nama_barang' => 'Pulpen Pilot G2',
            'kode_barang' => 'ATK-001',
            'stok' => 100,
            'harga' => 15000,
            'satuan' => 'Pcs',
        ]);
    }
}
