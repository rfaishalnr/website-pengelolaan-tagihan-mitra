<x-filament-panels::page>
    
    {{-- Bagian Form Input --}}
    <form wire:submit.prevent="submit" class="space-y-6">
        
        {{-- Memanggil schema form yang sudah dibuat di MitraWaspangInput.php --}}
        {{ $this->form }}

        {{-- Tombol Submit --}}
        <div class="flex flex-wrap items-center gap-4 justify-end mt-4">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-paper-airplane">
                Simpan Data Waspang
            </x-filament::button>
        </div>
    </form>

    <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>

    {{-- Bagian Tabel (Daftar Waspang) --}}
    <div class="mt-8">
        <h3 class="text-lg font-medium tracking-tight text-gray-950 dark:text-white mb-4">
            Daftar Waspang Perusahaan Anda
        </h3>
        
        {{-- Memanggil tabel yang sudah di-filter khusus untuk Mitra --}}
        {{ $this->table }}
    </div>

</x-filament-panels::page>