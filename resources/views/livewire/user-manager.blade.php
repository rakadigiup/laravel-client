<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component
{
    use WithPagination;

    public $search = '';
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|email|unique:users,email')]
    public $email = '';
    
    #[Validate('required|min:8')]
    public $password = '';
    
    #[Validate('required|string')]
    public $role = 'user';

    public $editingUserId = null;

    public function save()
    {
        if ($this->editingUserId) {
            $this->validateOnly('name');
            $this->validateOnly('email', ['email' => 'required|email|unique:users,email,' . $this->editingUserId]);
            $this->validateOnly('role');
            
            $user = User::find($this->editingUserId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
            ]);

            if ($this->password) {
                $this->validateOnly('password');
                $user->update(['password' => Hash::make($this->password)]);
            }
        } else {
            $this->validate();
            
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
            ]);
        }

        $this->resetFields();
        $this->dispatch('user-saved');
        session()->flash('status', 'User berhasil disimpan.');
    }

    public function edit(User $user)
    {
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = ''; // Clear password field on edit
    }

    public function delete(User $user)
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menghapus diri sendiri.');
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
        return [
            'users' => User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div class="w-1/3">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Cari user..." />
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
                @foreach ($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell class="font-medium">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$user->role === 'admin' ? 'indigo' : 'zinc'">
                                {{ strtoupper($user->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button.group>
                                <flux:modal.trigger name="user-modal">
                                    <flux:button variant="ghost" icon="pencil-square" wire:click="edit({{ $user->id }})" />
                                </flux:modal.trigger>
                                <flux:button variant="ghost" icon="trash" wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?" />
                            </flux:button.group>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="user-modal" class="md:w-[500px]">
        <form wire:submit="save" class="space-y-6">
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
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
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