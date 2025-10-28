<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tabs Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button
                    wire:click="$set('activeTab', 'plans')"
                    class="{{ $activeTab === 'plans' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                >
                    الباقات
                </button>
                <button
                    wire:click="$set('activeTab', 'features')"
                    class="{{ $activeTab === 'features' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                >
                    الميزات
                </button>
                <button
                    wire:click="$set('activeTab', 'pricing_tiers')"
                    class="{{ $activeTab === 'pricing_tiers' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
                >
                    رسوم المعاملات
                </button>
            </nav>
        </div>

        {{-- Content --}}
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
