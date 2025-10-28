<?php
/**
 * NUCLEAR CACHE CLEAR - This will clear EVERYTHING
 */

echo "<h1>🔥 FORCE CLEARING ALL CACHES...</h1>";

// 1. Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ OPcache CLEARED</p>";
} else {
    echo "<p>⚠️ OPcache not enabled</p>";
}

// 2. Clear Realpath Cache
clearstatcache(true);
echo "<p>✅ Realpath cache CLEARED</p>";

// 3. Show what class will be loaded
echo "<h2>Testing Import Class...</h2>";

require __DIR__ . '/../vendor/autoload.php';

if (class_exists('App\Imports\AllDataImport')) {
    echo "<p>✅ AllDataImport class found!</p>";

    $reflection = new ReflectionClass('App\Imports\AllDataImport');
    echo "<p>📁 File location: " . $reflection->getFileName() . "</p>";

    // Check if it has validation
    if ($reflection->hasMethod('rules')) {
        $method = $reflection->getMethod('rules');
        $obj = new App\Imports\AllDataImport();
        $rules = $obj->rules();

        echo "<p>Validation rules: " . (empty($rules) ? '<strong>NONE (Good!)</strong>' : '<span style="color:red;">HAS RULES (Bad!)</span>') . "</p>";
        if (!empty($rules)) {
            echo "<pre>Rules: " . print_r($rules, true) . "</pre>";
        }
    }
} else {
    echo "<p>❌ AllDataImport class NOT found!</p>";
}

echo "<hr>";
echo "<h2>✅ All caches cleared!</h2>";
echo "<p><strong>NOW:</strong></p>";
echo "<ol>";
echo "<li>Close your browser completely</li>";
echo "<li>Restart XAMPP Apache</li>";
echo "<li>Open a new browser window</li>";
echo "<li>Go to <a href='/import/all-data'>/import/all-data</a></li>";
echo "<li>Try the import again</li>";
echo "</ol>";
