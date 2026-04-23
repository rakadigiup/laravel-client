<?php

use Livewire\Volt\Component;
use App\Models\Item;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component
{
    use WithPagination;

    public $search = '';
    
    #[Validate('required|exists:categories,id')]
    public $category_id = '';
    
    #[Validate('required|string|max:255')]
    public $nama_barang = '';
    
    #[Validate('required|string|max:255|unique:items,kode_barang')]
    public $kode_barang = '';
    
    #[Validate('required|integer|min:0')]
    public $stok = 0;
    
    #[Validate('required|numeric|min:0')]
    public $harga = 0;
    
    #[Validate('required|string|max:50')]
    public $satuan = '';

    public $editingItemId = null;

    public function save()
    {
        if ($this->editingItemId) {
            $this->validateOnly('category_id');
            $this->validateOnly('nama_barang');
            $this->validateOnly('kode_barang', ['kode_barang' => 'required|string|max:255|unique:items,kode_barang,' . $this->editingItemId]);
            $this->validateOnly('stok');
            $this->validateOnly('harga');
            $this->validateOnly('satuan');

            Item::find($this->editingItemId)->update([
                'category_id' => $this->category_id,
                'nama_barang' => $this->nama_barang,
                'kode_barang' => $this->kode_barang,
                'stok' => $this->stok,
                'harga' => $this->harga,
                'satuan' => $this->satuan,
            ]);
        } else {
            $this->validate();
            Item::create([
                'category_id' => $this->category_id,
                'nama_barang' => $this->nama_barang,
                'kode_barang' => $this->kode_barang,
                'stok' => $this->stok,
                'harga' => $this->harga,
                'satuan' => $this->satuan,
            ]);
        }

        $this->resetFields();
        session()->flash('status', 'Barang berhasil disimpan.');
    }

    public function edit(Item $item)
    {
        $this->editingItemId = $item->id;
        $this->category_id = $item->category_id;
        $this->nama_barang = $item->nama_barang;
        $this->kode_barang = $item->kode_barang;
        $this->stok = $item->stok;
        $this->harga = $item->harga;
        $this->satuan = $item->satuan;
    }

    public function delete(Item $item)
    {
        $item->delete();
        session()->flash('status', 'Barang berhasil dihapus.');
    }

    public function resetFields()
    {
        $this->reset(['category_id', 'nama_barang', 'kode_barang', 'stok', 'harga', 'satuan', 'editingItemId']);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'items' => Item::with('category')
                ->where('nama_barang', 'like', '%' . $this->search . '%')
                ->orWhere('kode_barang', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
            'categories' => Category::all(),
        ];
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div class="w-1/3">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Cari barang atau SKU..." />
        </div>
        <flux:modal.trigger name="item-modal">
            <flux:button variant="primary" icon="plus" wire:click="resetFields">Tambah Barang</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="relative">
        <div wire:loading class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 z-10 flex items-center justify-center">
            <flux:icon.loading />
        </div>

        <flux:table :paginate="$items">
            <flux:table.columns>
                <flux:table.column>Kode (SKU)</flux:table.column>
                <flux:table.column>Nama Barang</flux:table.column>
                <flux:table.column>Kategori</flux:table.column>
                <flux:table.column>Stok</flux:table.column>
                <flux:table.column>Harga</flux:table.column>
                <flux:table.column align="end">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($items as $item)
                    <flux:table.row :key="$item->id">
                        <flux:table.cell><span class="rounded bg-zinc-100 dark:bg-zinc-800 px-2 py-1 text-xs font-mono text-zinc-800 dark:text-zinc-200">{{ $item->kode_barang }}</span></flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $item->nama_barang }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $item->category->name }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="{{ $item->stok < 5 ? 'text-red-600 dark:text-red-500 font-bold' : '' }}">
                                {{ $item->stok }} {{ $item->satuan }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>Rp {{ number_format($item->harga, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button.group>
                                <flux:modal.trigger name="item-modal">
                                    <flux:button variant="ghost" icon="pencil-square" wire:click="edit({{ $item->id }})" />
                                </flux:modal.trigger>
                                <flux:button variant="ghost" icon="trash" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus barang ini?" />
                            </flux:button.group>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="item-modal" class="md:w-[600px]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingItemId ? 'Edit Barang' : 'Tambah Barang' }}</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label>Kategori</flux:label>
                    <flux:select wire:model="category_id" placeholder="Pilih kategori...">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category_id" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Nama Barang</flux:label>
                    <flux:input wire:model="nama_barang" placeholder="Contoh: Laptop ASUS ROG" />
                    <flux:error name="nama_barang" />
                </flux:field>

                <flux:field>
                    <flux:label>Kode Barang (SKU)</flux:label>
                    <flux:input wire:model="kode_barang" placeholder="LPT-001" />
                    <flux:error name="kode_barang" />
                </flux:field>

                <flux:field>
                    <flux:label>Satuan</flux:label>
                    <flux:input wire:model="satuan" placeholder="Pcs, Box, Kg..." />
                    <flux:error name="satuan" />
                </flux:field>

                <flux:field>
                    <flux:label>Stok Awal</flux:label>
                    <flux:input type="number" wire:model="stok" />
                    <flux:error name="stok" />
                </flux:field>

                <flux:field>
                    <flux:label>Harga Satuan</flux:label>
                    <flux:input type="number" wire:model="harga" prefix="Rp" />
                    <flux:error name="harga" />
                </flux:field>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>