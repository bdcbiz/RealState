<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Merged Availability</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .upload-area {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: #f8f9ff;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #764ba2;
            background: #f0f2ff;
        }
        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .file-input {
            display: none;
        }
        .file-name {
            margin-top: 15px;
            color: #667eea;
            font-weight: 600;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .format-info {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .format-info h3 {
            color: #2196F3;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .format-info ul {
            list-style: none;
            padding-left: 0;
        }
        .format-info li {
            padding: 5px 0;
            color: #555;
        }
        .format-info li:before {
            content: "• ";
            color: #2196F3;
            font-weight: bold;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Import Merged Availability</h1>
        <p class="subtitle">Upload an Excel file to import data into Sales or Units Availability</p>

        @if(session('success'))
            <div class="alert alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                ✗ {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>✗ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('import.merged-availability') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                <div class="upload-icon">📁</div>
                <p><strong>Click to select Excel file</strong></p>
                <p style="color: #999; font-size: 13px; margin-top: 5px;">or drag and drop here</p>
                <p class="file-name" id="fileName"></p>
            </div>

            <input type="file" name="file" id="fileInput" class="file-input" accept=".xlsx,.xls,.csv" required>

            <button type="submit" class="btn" id="submitBtn" disabled>
                Upload & Import
            </button>
        </form>

        <div class="format-info">
            <h3>📋 Excel File Format Requirements</h3>
            <ul>
                <li><strong>Required Columns:</strong> Project, Type/Category, Source</li>
                <li><strong>Source Values:</strong> Must be either "Sales" or "Units"</li>
                <li><strong>For Sales:</strong> Include category, stage, unit_type, unit_code, etc.</li>
                <li><strong>For Units:</strong> Include usage_type and total_units</li>
                <li><strong>File Types:</strong> .xlsx, .xls, or .csv (Max 10MB)</li>
            </ul>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');
        const uploadArea = document.querySelector('.upload-area');

        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                fileName.textContent = '📄 ' + this.files[0].name;
                submitBtn.disabled = false;
            }
        });

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#764ba2';
            this.style.background = '#f0f2ff';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#667eea';
            this.style.background = '#f8f9ff';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#667eea';
            this.style.background = '#f8f9ff';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileName.textContent = '📄 ' + files[0].name;
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
