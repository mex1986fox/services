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
            'dbname' => "shopphotos",
        ],
        'name_servers' => "shopsphoto",
    ],
    "closed_records" => [
        "paths" => [
            "/api/token/delete",
            "/api/token/update",
            "/api/dependencies/update",
        ],
    ],
];
