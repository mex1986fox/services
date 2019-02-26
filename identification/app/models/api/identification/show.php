<?php
namespace App\Models\Api\Identification;

use \Zend\Validator\Exception\RuntimeException as RuntimeException;

class Show
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
        return [
            "status"=> "ok", 
            "data" => [
                "id"=>25
            ]
          ];
    }
}
