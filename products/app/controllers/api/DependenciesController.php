<?php
namespace App\Controllers\Api;

use \App\Controllers\MainController;
use \App\Models\Api\Dependencies\Update as Update;

class DependenciesController extends MainController
{

    public function update($request, $response, $args)
    {
        $cont = $this->container;
        $reg = new Update($cont, $request, $response);
        $answer = $reg->run();
        if ($answer['status'] == "ok") {
            $response = $response->withJson($answer, 200);
        }
        if ($answer['status'] == "except") {
            $response = $response->withJson($answer, 400);
        }
        if ($answer['status'] == "error") {
            $response = $response->withJson($answer, 404);
        }
        return $response;
    }
}
