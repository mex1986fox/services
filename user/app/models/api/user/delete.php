<?php
namespace App\Models\Api\User;

use \App\Services\Structur\TokenStructur;

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
        // удаляет юзеров

        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }
            $accessToken = $p["access_token"];

            // проверяем параметры
            // записываем токен в структуру
            $accessToken = $p["access_token"];
            $token = new TokenStructur();
            $token->setToken($accessToken);
            //  проверяем токен
            $valid = $this->container['validators'];
            $vToken = $valid->TokenValidator;
            if (!$vToken->isValid($token)) {
                $exceptions["access_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }

            return ["status" => "ok",
                "data" => null,
            ];
        } catch (RuntimeException | \Exception $e) {
            $exceptions["massege"] = $e->getMessage();
            return [
                "status" => "except",
                "data" => $exceptions,
            ];
        }

    }
}
