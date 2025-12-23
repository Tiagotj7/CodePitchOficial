<?php
require 'db.php';

echo '<pre>';
echo "GOOGLE_CLIENT_ID: "   . var_export(getenv('GOOGLE_CLIENT_ID'), true)   . PHP_EOL;
echo "GOOGLE_CLIENT_SECRET: " . var_export(getenv('GOOGLE_CLIENT_SECRET'), true) . PHP_EOL;
echo "GOOGLE_REDIRECT_URI: " . var_export(getenv('GOOGLE_REDIRECT_URI'), true) . PHP_EOL;
echo '</pre>';