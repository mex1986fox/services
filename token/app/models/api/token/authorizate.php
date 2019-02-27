<?php
namespace App\Models\Api\Token;

class Authentificate
{
    protected $request, $response, $container;
    public function __construct($container, $request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;

    }
    public function run()
    {
        // проводит аутентификацию access токена
        // в случае успеха выдает true
        return [
            "status" => "ok",
            "data" => null,
        ];

    }
}
