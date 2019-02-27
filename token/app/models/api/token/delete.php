<?php
namespace App\Models\Api\Token;

use \Zend\Validator\Exception\RuntimeException as RuntimeException;

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
        // удаляет токен ы 
        // используя refresh token
        return [
            "status"=> "ok", 
            "data" => null
          ];
    }
}
