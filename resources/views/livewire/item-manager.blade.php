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
    
    public $kode_barang = '';
    
    #[Validate('required|integer|min:0')]
    public $stok = 0;
    
    #[Validate('required|numeric|min:0')]
    public $harga = 0;
    
    #[Validate('required|string|max:50')]
    public $satuan = '';

    public $editingItemId = null;

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $kodeRule = 'required|string|max:255|unique:items,kode_barang';
        if ($this->editingItemId) {
            $kodeRule .= ',' . $this->editingItemId;
        }

        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => $kodeRule,
            'stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
        ]);

        $data = [
            'category_id' => $this->category_id,
            'nama_barang' => $this->nama_barang,
            'kode_barang' => $this->kode_barang,
            'stok' => $this->stok,
            'harga' => $this->harga,
            'satuan' => $this->satuan,
        ];

        if ($this->editingItemId) {
            Item::findOrFail($this->editingItemId)->update($data);
        } else {
            Item::create($data);
        }

        $this->resetFields();
        $this->js('$flux.modal("item-modal").close()');
        session()->flash('status', 'Barang berhasil disimpan.');
    }

    public function edit(Item $item)
    {
        $this->editingItemId = $item->id;
        $this->category_id = (string) $item->category_id; // Cast to string for select binding
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
        $search = $this->search;

        return [
            'items' => Item::with('category')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nama_barang', 'like', '%' . $search . '%')
                          ->orWhere('kode_barang', 'like', '%' . $search . '%');
                    });
                })
                ->latest()
                ->paginate(10),
            'categories' => Category::orderBy('name')->get(),
        ];
    }
};
?>

<div>
    @if (session('status'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div class="w-1/3">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari barang atau SKU..." />
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
                @forelse ($items as $item)
                    <flux:table.row wire:key="{{ $item->id }}">
                        <flux:table.cell><span class="rounded bg-zinc-100 dark:bg-zinc-800 px-2 py-1 text-xs font-mono text-zinc-800 dark:text-zinc-200">{{ $item->kode_barang }}</span></flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $item->nama_barang }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $item->category->name ?? '-' }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="{{ $item->stok < 5 ? 'text-red-600 dark:text-red-500 font-bold' : '' }}">
                                {{ $item->stok }} {{ $item->satuan }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>Rp {{ number_format($item->harga, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="item-modal">
                                    <flux:button size="sm" variant="subtle" icon="pencil-square" wire:click="edit({{ $item->id }})">Edit</flux:button>
                                </flux:modal.trigger>
                                <flux:button size="sm" variant="subtle" icon="trash" color="red" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus barang ini?">Hapus</flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">Belum ada data barang.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="item-modal" class="md:w-[600px]">
        <form wire:submit.prevent="save" method="POST" class="space-y-6">
            <flux:heading size="lg">{{ $editingItemId ? 'Edit Barang' : 'Tambah Barang' }}</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label>Kategori</flux:label>
                    <flux:select wire:model="category_id" placeholder="Pilih kategori...">
                        @foreach ($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
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