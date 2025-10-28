<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import All Data - Real Estate API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-2xl w-full">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 Import All Data</h1>
                <p class="text-gray-600">Upload Excel file to import Units, Compounds, and Companies data</p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('import.all-data') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Excel File
                    </label>
                    <input 
                        type="file" 
                        name="file" 
                        accept=".xlsx,.xls,.csv"
                        required
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                        Accepted formats: .xlsx, .xls, .csv (Max size: 50MB)
                    </p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 mb-2">📋 File Requirements:</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Excel file with columns matching the export format</li>
                        <li>• First row should contain column headers</li>
                        <li>• Unit Code must be unique</li>
                        <li>• Company and Compound will be matched by name</li>
                    </ul>
                </div>

                <div class="flex gap-4">
                    <button 
                        type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200"
                    >
                        📤 Upload and Import
                    </button>
                    <a 
                        href="{{ url('/admin/all-data') }}"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg text-center transition duration-200"
                    >
                        ← Back to All Data
                    </a>
                </div>
            </form>

            <div class="mt-8 text-center">
                <a 
                    href="{{ route('export.comprehensive-data') }}"
                    class="text-blue-600 hover:text-blue-800 text-sm"
                >
                    📥 Download template file (Export current data)
                </a>
            </div>
        </div>
    </div>
</body>
</html>
