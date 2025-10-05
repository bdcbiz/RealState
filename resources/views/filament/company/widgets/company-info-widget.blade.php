<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-4">
            @if($logoUrl)
                <div class="flex-shrink-0">
                    <img src="{{ $logoUrl }}"
                         alt="{{ $company->name ?? 'Company Logo' }}"
                         class="h-16 w-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                </div>
            @endif

            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Welcome to {{ $company ? $company->name : 'Company Panel' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->name }} • {{ $user->email }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
