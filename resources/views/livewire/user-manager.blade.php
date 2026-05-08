<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component
{
    use WithPagination;

    public $search = '';
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    public $email = '';
    
    public $password = '';
    
    #[Validate('required|string|in:admin,user')]
    public $role = 'user';

    public $editingUserId = null;

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $emailRule = 'required|email|unique:users,email';
        if ($this->editingUserId) {
            $emailRule .= ',' . $this->editingUserId;
        }

        $passwordRule = $this->editingUserId ? 'nullable|min:8' : 'required|min:8';

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $passwordRule,
            'role' => 'required|string|in:admin,user',
        ]);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
            ]);

            if (!empty($this->password)) {
                $user->update(['password' => $this->password]);
            }
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'role' => $this->role,
            ]);
        }

        $this->resetFields();
        $this->js('$flux.modal("user-modal").close()');
        session()->flash('status', 'User berhasil disimpan.');
    }

    public function edit(User $user)
    {
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
    }

    public function delete(User $user)
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menghapus akun sendiri.');
            return;
        }

        $user->delete();
        session()->flash('status', 'User berhasil dihapus.');
    }

    public function resetFields()
    {
        $this->reset(['name', 'email', 'password', 'role', 'editingUserId']);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        $search = $this->search;

        return [
            'users' => User::when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%');
                    });
                })
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
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari user..." />
        </div>
        <flux:modal.trigger name="user-modal">
            <flux:button variant="primary" icon="plus" wire:click="resetFields">Tambah User</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="relative">
        <div wire:loading class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 z-10 flex items-center justify-center">
            <flux:icon.loading />
        </div>

        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column>Nama</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column align="end">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row wire:key="{{ $user->id }}">
                        <flux:table.cell class="font-medium">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$user->role === 'admin' ? 'indigo' : 'zinc'">
                                {{ strtoupper($user->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="user-modal">
                                    <flux:button size="sm" variant="subtle" icon="pencil-square" wire:click="edit({{ $user->id }})">Edit</flux:button>
                                </flux:modal.trigger>
                                @if ($user->id !== auth()->id())
                                    <flux:button size="sm" variant="subtle" icon="trash" color="red" wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?">Hapus</flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-500">Belum ada data user.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="user-modal" class="md:w-[500px]">
        <form wire:submit.prevent="save" method="POST" class="space-y-6">
            <flux:heading size="lg">{{ $editingUserId ? 'Edit User' : 'Tambah User' }}</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Nama Lengkap</flux:label>
                    <flux:input wire:model="name" placeholder="John Doe" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Alamat Email</flux:label>
                    <flux:input type="email" wire:model="email" placeholder="john@example.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Password {{ $editingUserId ? '(Kosongkan jika tidak ingin diubah)' : '' }}</flux:label>
                    <flux:input type="password" wire:model="password" />
                    <flux:error name="password" />
                </flux:field>

                <flux:field>
                    <flux:label>Role</flux:label>
                    <flux:select wire:model="role">
                        <flux:select.option value="user">User</flux:select.option>
                        <flux:select.option value="admin">Admin</flux:select.option>
                    </flux:select>
                    <flux:error name="role" />
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