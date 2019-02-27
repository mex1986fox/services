<?php
namespace App\Models\Api\Token;

class Delete
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
        // удаляет токены refresh и access
        // используя refresh token
        return [
            "status" => "ok",
            "data" => null,
        ];
    }
}
