<?php
// trusted_services - доверенные ip на которых размещены наши сервисы
return [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'db' => [
            'host' => "127.0.0.1",
            'port' => '5432',
            'user' => "suser",
            'pass' => "suser",
            'dbname' => "users",
        ],
    ],
    "services" => [
        "token" => ["sheme" => "http", "host" => "token.ru", "port" => 8081],
        "asynchreq" => ["sheme" => "http", "host" => "asynchreq.ru", "port" => 8083],
        "user" => ["sheme" => "http", "host" => "user.ru", "port" => 8082],
        "dependencies" => ["sheme" => "http", "host" => "dependencies.ru", "port" => 8084],
    ],
];
