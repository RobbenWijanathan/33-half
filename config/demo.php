<?php

return [
    'admin_name' => env('DEV_ADMIN_NAME', 'Store Admin'),
    'admin_email' => env('DEV_ADMIN_EMAIL'),
    'admin_password' => env('DEV_ADMIN_PASSWORD'),
    'seed_samples' => env('SEED_DEMO_DATA', false),
    'user_password' => env('DEMO_USER_PASSWORD'),
];
