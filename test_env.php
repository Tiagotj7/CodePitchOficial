<?php
require 'db.php';

echo 'CLIENT_ID: ' . getenv('GOOGLE_CLIENT_ID') . "<br>";
echo 'CLIENT_SECRET: ' . getenv('GOOGLE_CLIENT_SECRET') . "<br>";
echo 'REDIRECT_URI: ' . getenv('GOOGLE_REDIRECT_URI') . "<br>";