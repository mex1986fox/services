<?php
namespace App\Controllers\Api;

use \App\Controllers\MainController;
use \App\Models\Api\Like\Create as Create;

class LikeController extends MainController
{
    public function create($request, $response, $args)
    {
        $cont = $this->container;
        $reg = new Create($cont, $request, $response);
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
