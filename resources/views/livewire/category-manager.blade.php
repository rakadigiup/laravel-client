<?php

use Livewire\Volt\Component;
use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component
{
    use WithPagination;

    public $search = '';
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    public $slug = '';
    
    #[Validate('nullable|string')]
    public $description = '';

    public $editingCategoryId = null;

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Auto-generate slug from name.
     */
    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    public function save()
    {
        $slugRule = 'required|string|max:255|unique:categories,slug';
        if ($this->editingCategoryId) {
            $slugRule .= ',' . $this->editingCategoryId;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => $slugRule,
            'description' => 'nullable|string',
        ]);

        if ($this->editingCategoryId) {
            $category = Category::findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
            ]);
        } else {
            Category::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
            ]);
        }

        $this->resetFields();
        $this->js('$flux.modal("category-modal").close()');
        session()->flash('status', 'Kategori berhasil disimpan.');
    }

    public function edit(Category $category)
    {
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
    }

    public function delete(Category $category)
    {
        if ($category->items()->exists()) {
            session()->flash('error', 'Kategori tidak bisa dihapus karena masih memiliki relasi ke data barang.');
            return;
        }

        $category->delete();
        session()->flash('status', 'Kategori berhasil dihapus.');
    }

    public function resetFields()
    {
        $this->reset(['name', 'slug', 'description', 'editingCategoryId']);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'categories' => Category::withCount('items')
                ->where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
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

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div class="w-1/3">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari kategori..." />
        </div>
        <flux:modal.trigger name="category-modal">
            <flux:button variant="primary" icon="plus" wire:click="resetFields">Tambah Kategori</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="relative">
        <div wire:loading class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 z-10 flex items-center justify-center">
            <flux:icon.loading />
        </div>

        <flux:table :paginate="$categories">
            <flux:table.columns>
                <flux:table.column>Nama</flux:table.column>
                <flux:table.column>Slug</flux:column>
                <flux:table.column>Deskripsi</flux:table.column>
                <flux:table.column>Jumlah Barang</flux:table.column>
                <flux:table.column align="end">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($categories as $category)
                    <flux:table.row wire:key="{{ $category->id }}">
                        <flux:table.cell class="font-medium">{{ $category->name }}</flux:table.cell>
                        <flux:table.cell>{{ $category->slug }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ Str::limit($category->description, 50) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $category->items_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="category-modal">
                                    <flux:button size="sm" variant="subtle" icon="pencil-square" wire:click="edit({{ $category->id }})">Edit</flux:button>
                                </flux:modal.trigger>
                                <flux:button size="sm" variant="subtle" icon="trash" color="red" wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus kategori ini?">Hapus</flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">Belum ada data kategori.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="category-modal" class="md:w-[500px]">
        <form wire:submit.prevent="save" method="POST" class="space-y-6">
            <flux:heading size="lg">{{ $editingCategoryId ? 'Edit Kategori' : 'Tambah Kategori' }}</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Nama Kategori</flux:label>
                    <flux:input wire:model.live="name" placeholder="Contoh: Elektronik" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input wire:model="slug" placeholder="otomatis-terisi" readonly />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea wire:model="description" placeholder="Penjelasan singkat kategori..." />
                    <flux:error name="description" />
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