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
    
    #[Validate('required|string|max:255|unique:categories,slug')]
    public $slug = '';
    
    #[Validate('nullable|string')]
    public $description = '';

    public $editingCategoryId = null;

    public function updatedName($value)
    {
        if (!$this->editingCategoryId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        if ($this->editingCategoryId) {
            $this->validateOnly('name');
            $this->validateOnly('slug', ['slug' => 'required|string|max:255|unique:categories,slug,' . $this->editingCategoryId]);
            $this->validateOnly('description');

            Category::find($this->editingCategoryId)->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
            ]);
        } else {
            $this->validate();
            Category::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
            ]);
        }

        $this->resetFields();
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
        if ($category->items()->count() > 0) {
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
            'categories' => Category::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div class="w-1/3">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Cari kategori..." />
        </div>
        <flux:modal.trigger name="category-modal">
            <flux:button variant="primary" icon="plus" wire:click="resetFields">Tambah Kategori</flux:button>
        </flux:modal.trigger>
    </div>

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="relative">
        <div wire:loading class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 z-10 flex items-center justify-center">
            <flux:icon.loading />
        </div>

        <flux:table :paginate="$categories">
            <flux:table.columns>
                <flux:table.column>Nama</flux:table.column>
                <flux:table.column>Slug</flux:table.column>
                <flux:table.column>Deskripsi</flux:table.column>
                <flux:table.column align="end">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($categories as $category)
                    <flux:table.row :key="$category->id">
                        <flux:table.cell class="font-medium">{{ $category->name }}</flux:table.cell>
                        <flux:table.cell>{{ $category->slug }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ Str::limit($category->description, 50) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button.group>
                                <flux:modal.trigger name="category-modal">
                                    <flux:button variant="ghost" icon="pencil-square" wire:click="edit({{ $category->id }})" />
                                </flux:modal.trigger>
                                <flux:button variant="ghost" icon="trash" wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus kategori ini?" />
                            </flux:button.group>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="category-modal" class="md:w-[500px]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingCategoryId ? 'Edit Kategori' : 'Tambah Kategori' }}</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Nama Kategori</flux:label>
                    <flux:input wire:model.live="name" placeholder="Contoh: Elektronik" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input wire:model="slug" placeholder="otomatis-terisi" />
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