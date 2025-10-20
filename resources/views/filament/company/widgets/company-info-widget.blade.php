<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $data = $this->getViewData();
            $company = $data['company'];
            $logoUrl = $data['logoUrl'];
        @endphp

        <div class="flex items-center space-x-4">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $company->name }}" class="h-16 w-16 rounded-lg object-cover">
            @endif

            <div class="flex-1">
                <h2 class="text-2xl font-bold">{{ $company->name }}</h2>
                @if($company->email)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $company->email }}</p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
