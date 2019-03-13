<?php
// ip - доверенные ip на которых размещены наши сервисы
// paths - маршруты к которым следует применить проверку доверенных IP
return [
    "ip" => ["192.168.20.230", "127.0.0.1"],
    "paths" => [
        "/api/token/delete",
        "/api/token/update",
        "/api/dependencies/update",
        // "/api/token/authorizate"
    ],
];
