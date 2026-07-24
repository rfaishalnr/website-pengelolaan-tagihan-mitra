<x-filament::page>
    <div class="space-y-6">
        <!-- Form Input -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Tambah Data Pejabat
            </h2>
            
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button wire:click="submit" size="lg">
                    {{-- <x-heroicon-o-plus class="w-5 h-5 mr-2"/> --}}
                    Simpan Data
                </x-filament::button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Data Pejabat yang Tersimpan
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Daftar semua pejabat yang telah ditambahkan ke sistem
                </p>
            </div>
            
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament::page>