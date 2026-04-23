<x-layouts.app>
    <div class="space-y-6">
        <flux:heading size="xl" level="1">Dashboard</flux:heading>

        <flux:card>
            <div class="p-6 text-zinc-800 dark:text-zinc-200">
                {{ __("Selamat Datang! Anda berhasil login ke Sistem Informasi Gudang.") }}
            </div>
        </flux:card>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <flux:card class="flex flex-col items-center justify-center p-8 space-y-4">
                <flux:icon name="squares-2x2" class="size-12 text-indigo-500" />
                <flux:heading size="lg">Kategori</flux:heading>
                <flux:button variant="ghost" :href="route('categories')" wire:navigate>Kelola Kategori</flux:button>
            </flux:card>

            <flux:card class="flex flex-col items-center justify-center p-8 space-y-4">
                <flux:icon name="archive-box" class="size-12 text-indigo-500" />
                <flux:heading size="lg">Data Barang</flux:heading>
                <flux:button variant="ghost" :href="route('items')" wire:navigate>Kelola Barang</flux:button>
            </flux:card>

            <flux:card class="flex flex-col items-center justify-center p-8 space-y-4">
                <flux:icon name="users" class="size-12 text-indigo-500" />
                <flux:heading size="lg">User</flux:heading>
                <flux:button variant="ghost" :href="route('users')" wire:navigate>Kelola User</flux:button>
            </flux:card>
        </div>
    </div>
</x-layouts.app>
