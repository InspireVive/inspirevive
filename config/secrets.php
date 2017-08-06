<?php

use Infuse\Email\Driver\MandrillDriver;

return [
    'app' => [
        'email' => 'j@jaredtking.com',
        'salt' => 'iMbXPdbI/H233V57tzec5PU8wwgP8Vv3i6j8gCzVN11QOhMB8ecQaDTG0M+PI5uMeW0zWO0RgenkkdtuuAw98A==',
    ],
    'email' => [
        'from_email' => 'hello@inspirevive.com',
        'from_name' => 'InspireVive',
        'driver' => MandrillDriver::class,
        'key' => 'tiee72AnVLwLyv3zPtQnIQ',
    ]
];