<?php
// trusted_services - доверенные ip на которых размещены наши сервисы
$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'trusted_services' => ["127.0.0.1"],
        'db' => [
            'host' => "127.0.0.1",
            'port' => '5432',
            'user' => "user",
            'pass' => "user",
            'dbname' => "services",
        ],
    ],
];
