<?php
// deploy.php
header('Content-Type: text/plain');

// Same token you set in GitHub Secrets (DEPLOY_TOKEN)
$token = "696d0f801088e47fba20f195d18300464db9bc30fcf406e2cdfed073e35466fo";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

if (!isset($_SERVER['HTTP_X_DEPLOY_TOKEN']) || $_SERVER['HTTP_X_DEPLOY_TOKEN'] !== $token) {
    http_response_code(403);
    exit("Forbidden: Invalid token");
}

echo "✅ Deploy hook triggered. Files uploaded.\n";
echo "Running Laravel post-deploy tasks...\n\n";

// Make sure we are in the Laravel root directory
chdir(__DIR__);

// Function to run shell commands safely
function run($cmd) {
    echo "> $cmd\n";
    $output = shell_exec($cmd . ' 2>&1');
    echo $output . "\n";
}

// Run composer dump-autoload (if composer is available on server)
if (file_exists('composer.json')) {
    run('composer dump-autoload -o');
}

// Run artisan commands
if (file_exists('artisan')) {
    run('php artisan migrate --force');
    run('php artisan config:clear');
    run('php artisan cache:clear');
    run('php artisan view:clear');
    run('php artisan route:clear');
    run('php artisan optimize');
}

echo "🚀 Deployment finished!\n";
