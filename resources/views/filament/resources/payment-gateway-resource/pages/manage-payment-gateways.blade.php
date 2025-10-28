<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tabs Navigation --}}
        <div class="flex flex-wrap gap-2 bg-white dark:bg-gray-800 rounded-lg p-2 shadow">
            <button
                wire:click="$set('activeTab', 'paysky')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md transition-colors flex-1 min-w-[120px]',
                    'bg-primary-600 text-white' => $activeTab === 'paysky',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'paysky',
                ])
            >
                💳 PaySky
            </button>

            <button
                wire:click="$set('activeTab', 'easykash')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md transition-colors flex-1 min-w-[120px]',
                    'bg-primary-600 text-white' => $activeTab === 'easykash',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'easykash',
                ])
            >
                💰 EasyKash
            </button>

            <button
                wire:click="$set('activeTab', 'afs')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md transition-colors flex-1 min-w-[120px]',
                    'bg-primary-600 text-white' => $activeTab === 'afs',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'afs',
                ])
            >
                🏦 AFS Mastercard
            </button>

            <button
                wire:click="$set('activeTab', 'paymob')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md transition-colors flex-1 min-w-[120px]',
                    'bg-primary-600 text-white' => $activeTab === 'paymob',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'paymob',
                ])
            >
                📱 PayMob
            </button>

            <button
                wire:click="$set('activeTab', 'fawry')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md transition-colors flex-1 min-w-[120px]',
                    'bg-primary-600 text-white' => $activeTab === 'fawry',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'fawry',
                ])
            >
                🛒 Fawry
            </button>

            <button
                wire:click="$set('activeTab', 'geidea')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md transition-colors flex-1 min-w-[120px]',
                    'bg-primary-600 text-white' => $activeTab === 'geidea',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'geidea',
                ])
            >
                🇸🇦 Geidea
            </button>

            <button
                wire:click="$set('activeTab', 'stripe')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-md transition-colors flex-1 min-w-[120px]',
                    'bg-primary-600 text-white' => $activeTab === 'stripe',
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => $activeTab !== 'stripe',
                ])
            >
                🌍 Stripe
            </button>
        </div>

        {{-- Tab Content --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            @if($activeTab === 'paysky')
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        إعدادات PaySky
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        بوابة الدفع المصرية - PaySky
                    </p>
                    {{ $this->payskyForm }}
                </div>
            @elseif($activeTab === 'easykash')
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        إعدادات EasyKash
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        بوابة الدفع المصرية - EasyKash
                    </p>
                    {{ $this->easykashForm }}
                </div>
            @elseif($activeTab === 'afs')
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        إعدادات AFS Mastercard
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        بوابة Mastercard Gateway - AFS
                    </p>
                    {{ $this->afsForm }}
                </div>
            @elseif($activeTab === 'paymob')
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        إعدادات PayMob
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        بوابة الدفع المصرية - PayMob
                    </p>
                    {{ $this->paymobForm }}
                </div>
            @elseif($activeTab === 'fawry')
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        إعدادات Fawry
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        بوابة الدفع المصرية - Fawry
                    </p>
                    {{ $this->fawryForm }}
                </div>
            @elseif($activeTab === 'geidea')
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        إعدادات Geidea
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        بوابة الدفع الخليجية - Geidea (السعودية، الإمارات، الكويت)
                    </p>
                    {{ $this->geideaForm }}
                </div>
            @else
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        إعدادات Stripe
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        بوابة الدفع العالمية - Stripe
                    </p>
                    {{ $this->stripeForm }}
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <x-filament::button
                wire:click="save{{ ucfirst($activeTab) }}"
                color="primary"
                size="lg"
            >
                حفظ الإعدادات
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
