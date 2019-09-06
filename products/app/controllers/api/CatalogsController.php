<?php
namespace App\Controllers\Api;

use \App\Controllers\MainController;
use \App\Models\Api\Catalogs\Create as Create;
use \App\Models\Api\Catalogs\Delete as Delete;
use \App\Models\Api\Catalogs\Show as Show;
use \App\Models\Api\Catalogs\Update as Update;

class CatalogsController extends MainController
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
    public function delete($request, $response, $args)
    {
        $cont = $this->container;
        $reg = new Delete($cont, $request, $response);
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
