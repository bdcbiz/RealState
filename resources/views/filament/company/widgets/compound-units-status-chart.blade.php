<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <h2 class="text-lg font-semibold">Compounds Units Status</h2>

            @php
                $compounds = $this->getCompoundsData();
            @endphp

            @if(count($compounds) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-4">Project</th>
                                <th class="text-right py-2 px-4">Total Units</th>
                                <th class="text-right py-2 px-4">Sold</th>
                                <th class="text-right py-2 px-4">Available</th>
                                <th class="text-right py-2 px-4">Inhabited</th>
                                <th class="text-right py-2 px-4">In Progress</th>
                                <th class="text-right py-2 px-4">Delivered</th>
                                <th class="text-right py-2 px-4">Sales Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compounds as $compound)
                                <tr class="border-b dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="py-2 px-4 font-medium">{{ $compound['project'] }}</td>
                                    <td class="py-2 px-4 text-right">{{ $compound['total_units'] }}</td>
                                    <td class="py-2 px-4 text-right text-green-600 dark:text-green-400">{{ $compound['sold_units'] }}</td>
                                    <td class="py-2 px-4 text-right text-blue-600 dark:text-blue-400">{{ $compound['available_units'] }}</td>
                                    <td class="py-2 px-4 text-right">{{ $compound['inhabited_units'] }}</td>
                                    <td class="py-2 px-4 text-right text-orange-600 dark:text-orange-400">{{ $compound['in_progress_units'] }}</td>
                                    <td class="py-2 px-4 text-right text-purple-600 dark:text-purple-400">{{ $compound['delivered_units'] }}</td>
                                    <td class="py-2 px-4 text-right font-semibold">{{ $compound['sales_count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    No compounds data available
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
