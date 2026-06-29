<?php
echo "ENV FILE EXISTS: " . (file_exists(dirname(__DIR__) . '/.env') ? 'YES' : 'NO') . "<br>";
echo "APP_URL from getenv: " . getenv('APP_URL') . "<br>";
echo "APP_ENV from getenv: " . getenv('APP_ENV') . "<br>";

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    echo "<pre>";
    echo htmlspecialchars(file_get_contents($envFile));
    echo "</pre>";
} else {
    echo "No .env file found at: " . $envFile;
}
