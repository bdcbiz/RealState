<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">📊 Export Data</h2>
            
            <div class="space-y-4">
                <!-- Export Comprehensive Data -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h3 class="font-semibold text-lg mb-2">🔵 Comprehensive Data Export</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Export all units, compounds, and companies data to Excel
                    </p>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mb-4">
                        <li>✓ <strong>5,467 Units</strong> with full details</li>
                        <li>✓ <strong>1,360 Compounds</strong> with complete information</li>
                        <li>✓ <strong>557 Companies</strong> with all data</li>
                        <li>✓ <strong>89 Columns</strong> in Excel file</li>
                    </ul>
                    <a href="{{ route('export.comprehensive-data') }}" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                        </svg>
                        Download Excel File
                    </a>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">5,467</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Units</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">1,360</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Compounds</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">557</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Companies</div>
                    </div>
                </div>

                <!-- Information -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mt-6">
                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">ℹ️ Important Notes:</h4>
                    <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                        <li>• Large file - may take 1-2 minutes to generate</li>
                        <li>• UTF-8 encoding with BOM for Arabic support</li>
                        <li>• Data is fetched in real-time from database</li>
                        <li>• Includes all relationships (compound + company data)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
