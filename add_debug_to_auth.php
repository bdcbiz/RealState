<?php
$file = "/var/www/realestate/app/Http/Controllers/AuthController.php";
$content = file_get_contents($file);

// Check if debug already exists
if (strpos($content, "GOOGLE LOGIN DEBUG") !== false) {
    echo "Debug logging already exists!\n";
    exit(0);
}

// Add debug logging right after private function handleGoogleLogin line
$pattern = "/(private function handleGoogleLogin\(Request \\$request\)\s*\{)/";
$replacement = "$1\n        // ✅ DEBUG LOGGING\n"  .
"        \Log::info('========== GOOGLE LOGIN DEBUG ===========');\n" .
"        \Log::info('Request data:', \$request->all());\n" .
"        \Log::info('ID Token:', [\$request->id_token ?? 'NULL']);\n" .
"        if (\$request->id_token) {\n" .
"            \$token = \$request->id_token;\n" .
"            \Log::info('Token length: ' . strlen(\$token));\n" .
"            \Log::info('Token preview: ' . substr(\$token, 0, 50));\n" .
"            \Log::info('Starts with eyJ (JWT): ' . (strpos(\$token, 'eyJ') === 0 ? 'YES' : 'NO'));\n" .
"            \Log::info('Starts with ya29 (Access Token): ' . (strpos(\$token, 'ya29') === 0 ? 'YES - WRONG!' : 'NO'));\n" .
"            \Log::info('Segments: ' . count(explode('.' , \$token)));\n" .
"        }\n" .
"        \Log::info('=========================================');\n";

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent === $content) {
    echo "Failed to add debug logging - pattern not found\n";
    exit(1);
}

if (file_put_contents($file, $newContent)) {
    echo "✅ Debug logging added successfully!\n";
    exit(0);
} else {
    echo "❌ Failed to write file\n";
    exit(1);
}
