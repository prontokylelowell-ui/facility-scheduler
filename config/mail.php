<?php

return [

    'name' => env('APP_NAME', 'Resort Booking'),

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Resort Booking'),
    ],

    'markdown' => [
        'theme' => 'default',
        'paths' => [resource_path('views/vendor/mail')],
    ],

];
