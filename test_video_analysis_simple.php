<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Video Analysis System...\n";

try {
    // Test 1: Check if service can be instantiated
    echo "1. Testing VideoAnalysisService instantiation...\n";
    $service = app(\App\Services\VideoAnalysisService::class);
    echo "   ✓ Service loaded successfully\n";
    
    // Test 2: Check if controller can be instantiated
    echo "2. Testing FacebookAnalysisController instantiation...\n";
    $controller = app(\App\Http\Controllers\Api\FacebookAnalysisController::class);
    echo "   ✓ Controller loaded successfully\n";
    
    // Test 3: Check environment configuration
    echo "3. Testing environment configuration...\n";
    $geminiKey = config('services.gemini.api_key');
    if ($geminiKey) {
        echo "   ✓ GEMINI_API_KEY is configured\n";
    } else {
        echo "   ✗ GEMINI_API_KEY is not configured\n";
    }
    
    // Test 4: Check if routes are registered
    echo "4. Testing route registration...\n";
    $routes = app('router')->getRoutes();
    $analyzeRoute = null;
    foreach ($routes as $route) {
        if ($route->getName() === 'api.facebook.analyze-video') {
            $analyzeRoute = $route;
            break;
        }
    }
    
    if ($analyzeRoute) {
        echo "   ✓ Analyze video route is registered\n";
        echo "   ✓ Route method: " . implode('|', $analyzeRoute->methods()) . "\n";
        echo "   ✓ Route URI: " . $analyzeRoute->uri() . "\n";
    } else {
        echo "   ✗ Analyze video route is not registered\n";
    }
    
    echo "\nAll tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
