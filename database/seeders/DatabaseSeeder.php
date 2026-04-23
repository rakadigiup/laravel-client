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
            'password' => bcrypt('password'),
            'role' => 'admin',
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
            'category_id' => $furniture->id,
            'nama_barang' => 'Kursi Gaming Secretlab',
            'kode_barang' => 'KRS-001',
            'stok' => 10,
            'harga' => 5000000,
            'satuan' => 'Unit',
        ]);
    }
}
