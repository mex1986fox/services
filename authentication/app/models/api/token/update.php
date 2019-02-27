<?php
namespace App\Models\Api\Token;

use \Zend\Validator\Exception\RuntimeException as RuntimeException;

class Update
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
          // обновляет токены 
        // используя refresh token
        return [
            "status"=> "ok", 
            "data" => null
          ];
    }
}
