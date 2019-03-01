<?php
namespace App\Controllers\Api;

use \App\Controllers\MainController;
use \App\Models\Api\Token\Authentificate as Authentificate;
use \App\Models\Api\Token\Authorizate as Authorizate;
use \App\Models\Api\Token\Create as Create;
use \App\Models\Api\Token\Delete as Delete;
use \App\Models\Api\Token\Update as Update;

class TokenController extends MainController
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
    public function authorizate($request, $response, $args)
    {
        $cont = $this->container;
        $reg = new Authorizate($cont, $request, $response);
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
    public function authentificate($request, $response, $args)
    {
        $cont = $this->container;
        $reg = new Authentificate($cont, $request, $response);
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
