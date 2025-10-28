<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tabs Navigation --}}
        <div class="flex space-x-1 space-x-reverse bg-white dark:bg-gray-800 rounded-lg p-1 shadow">
            <button
                wire:click="$set('activeTab', 'plans')"
                @class([
                    'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                    'bg-primary-600 text-white' => $activeTab === 'plans',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'plans',
                ])
            >
                📦 الباقات
            </button>

            <button
                wire:click="$set('activeTab', 'features')"
                @class([
                    'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                    'bg-primary-600 text-white' => $activeTab === 'features',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'features',
                ])
            >
                ✨ الميزات
            </button>

            <button
                wire:click="$set('activeTab', 'pricing_tiers')"
                @class([
                    'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                    'bg-primary-600 text-white' => $activeTab === 'pricing_tiers',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'pricing_tiers',
                ])
            >
                💰 رسوم المعاملات
            </button>
        </div>

        {{-- Tab Content --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6">
                @if($activeTab === 'plans')
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            إدارة باقات الاشتراك
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            يمكنك إضافة وتعديل باقات الاشتراك المتاحة للعملاء
                        </p>
                    </div>
                @elseif($activeTab === 'features')
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            إدارة الميزات
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            أضف الميزات التي ستكون متاحة في الباقات المختلفة
                        </p>
                    </div>
                @else
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            إدارة رسوم المعاملات
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            حدد رسوم المعاملات حسب نوع طريقة الدفع لكل باقة
                        </p>
                    </div>
                @endif
            </div>

            {{-- Table --}}
            <div class="border-t border-gray-200 dark:border-gray-700">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
