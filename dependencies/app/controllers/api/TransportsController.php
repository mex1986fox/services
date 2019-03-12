<?php
namespace App\Controllers\Api;

use \App\Controllers\MainController;
use \App\Models\Api\Transports\Show as Show;

class TransportsController extends MainController
{

    public function show($request, $response, $args)
    {
        $cont = $this->container;
        $reg = new Show($cont, $request, $response);
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
