<?php
namespace App\Models\Api\Token;

use \App\Services\Structur\TokenStructur;
use \Zend\Validator\Exception\RuntimeException as RuntimeException;

class Authentificate
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
        // проводит проверку логина и пароля
        // в случае успеха генерирует Access и Refresh токены
        // которые возвращает клиентской программе
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            $login = $p["login"];
            $password = $p["password"];

            // проверяем параметры
            $valid = $this->container['validators'];
            $vStLen = $valid->StringLength;
            if (empty($login)) {
                $exceptions["login"] = "Не указан";
            }
            if (empty($password)) {
                $exceptions["password"] = "Не указан";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах");
            }
            $vStLen->setMin(1);
            $vStLen->setMax(64);
            if (!$vStLen->isValid($login)) {
                $exceptions["login"] = "Не соответсвует диапозону длины";
            }
            $vStLen->setMin(4);
            $vStLen->setMax(32);
            if (!$vStLen->isValid($password)) {
                $exceptions["password"] = "Не соответсвует диапозону длины";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах");
            }

            // ищем базе
            $db = $this->container['db'];
            $q = "select * from tokens where login = '{$login}';";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                $exceptions["login"] = "Такой логин не зарегистрирован.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if ($user['password'] !== md5($password)) {
                $exceptions["password"] = "Не верный пароль.";
                throw new \Exception("Ошибки в параметрах.");
            }

            // создаем токены доступа
            $accessToken = new TokenStructur();
            $accessToken->initAccessToken($user["user_id"]);

            $refreshToken = new TokenStructur();
            $refreshToken->initRefreshToken($user["user_id"]);

            $q = "update tokens
                    set access_tokens = '{\"{$accessToken->getSignature()}\":\"{$accessToken->getSecretKey()}\"}'::jsonb,
                        refresh_tokens = '{\"{$refreshToken->getSignature()}\":\"{$refreshToken->getSecretKey()}\"}'::jsonb
                    where login = '{$login}' returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }
            // отправить запросы на обновление токенов у микросервисов
            $apiReqwests = $this->container['api-requests'];
            $rCreateToken = $apiReqwests->RequestUpdateTokens;
            $rCreateToken->go(["user_id" => $user["user_id"], "access_token" => $accessToken->getToken()]);

            return ["status" => "ok",
                "data" => [
                    "access_token" => $accessToken->getToken(),
                    "refresh_token" => $refreshToken->getToken(),
                ],
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
