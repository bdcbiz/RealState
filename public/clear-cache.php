<?php
/**
 * Clear all caches including OPcache
 * Access this file via: http://your-domain/clear-cache.php
 */

echo "<h1>Clearing Caches...</h1>";

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ OPcache cleared successfully!</p>";
} else {
    echo "<p>⚠️ OPcache not enabled</p>";
}

// Clear Realpath Cache
clearstatcache(true);
echo "<p>✅ Realpath cache cleared!</p>";

// Show OPcache status
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "<h2>OPcache Status:</h2>";
    echo "<pre>";
    echo "Enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
    echo "Cache Full: " . ($status['cache_full'] ? 'Yes' : 'No') . "\n";
    echo "Memory Used: " . number_format($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    echo "</pre>";
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Go back to your import page: <a href='/import/all-data'>/import/all-data</a></li>";
echo "<li>Try importing your Excel file again</li>";
echo "</ol>";

echo "<p><strong>Cache cleared at: " . date('Y-m-d H:i:s') . "</strong></p>";
