<?php
namespace App\Models\Api\Token;

use \App\Services\Structur\TokenStructur;

class Authorizate
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
        // проводит проверку access токена
        // в случае успеха выдает true
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            $accessToken = $p["access_token"];

            // проверяем параметры
            // указан ли токен
            if (empty($accessToken)) {
                $exceptions["access_token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $token = new TokenStructur();
            $token->setToken($accessToken);
            //ищем ключ от токена
            $db = $this->container['db'];
            $q = "select * from tokens where user_id = '{$token->getUserID()}';";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();
            if (!isset($user["user_id"])) {
                throw new \Exception("Такой пользователь не зарегистрирован.");
            }
            $TokenKey = current(json_decode($user["access_tokens"], 1));
            if (empty($TokenKey)) {
                throw new \Exception("Токен отсутствует у пользователя.");
            }

            // проверяем токен
            $valid = $this->container['validators'];
            $vToken = $valid->TokenValidator;
            $vToken->setKey($TokenKey);
            if (!$vToken->isValid($token)) {
                $exceptions["access_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (!$vToken->isValidLifeTime()) {
                $exceptions["access_token"] = "Истекло время жизни токена.";
                throw new \Exception("Ошибки в параметрах.");
            }

            return [
                "status" => "ok",
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
