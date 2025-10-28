<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing what happens when we call /api/login with Google...\n\n";

$testToken = "test.token.here";

echo "Simulating POST to /api/login with:\n";
echo json_encode([
    'login_method' => 'google',
    'id_token' => $testToken
], JSON_PRETTY_PRINT);
echo "\n\n";

// Check if route exists
$routes = app('router')->getRoutes();
$loginRoute = null;
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'login') && in_array('POST', $route->methods())) {
        echo "Found route: " . $route->uri() . " -> " . $route->getActionName() . "\n";
        $loginRoute = $route;
    }
}

if (!$loginRoute) {
    echo "❌ No login route found!\n";
} else {
    echo "✅ Login route exists\n";
}
