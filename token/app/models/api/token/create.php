<?php
namespace App\Models\Api\Token;

use \App\Services\Structur\TokenStructur;
use \Zend\Validator\Exception\RuntimeException as RuntimeException;

class Create
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

            //отправляем запрос к сервису пользователей
            $requests = $this->container["api-requests"];
            $requestsToUser = $requests->RequestToUser;
            $response = $requestsToUser->go("/api/user/authentificate", ["login" => $login, "password" => $password]);
            if ($response == false) {
                throw new \Exception("Ошибки на сервисе пользователей.");
            }
            $userID = $response->user_id;
            // создаем токены доступа
            $accessToken = new TokenStructur($this->container);
            $accessToken->initAccessToken($userID);

            $refreshToken = new TokenStructur($this->container);
            $refreshToken->initRefreshToken($userID);

            $db = $this->container['db'];
            $q =
                " insert into tokens (user_id, access_tokens, refresh_tokens) values " .
                " ({$userID},'{\"{$accessToken->getSignature()}\":\"{$accessToken->getSecretKey()}\"}'::jsonb, '{\"{$refreshToken->getSignature()}\":\"{$refreshToken->getSecretKey()}\"}'::jsonb) " .
                " on conflict (user_id) do " .
                " update set access_tokens = '{\"{$accessToken->getSignature()}\":\"{$accessToken->getSecretKey()}\"}'::jsonb, " .
                " refresh_tokens = '{\"{$refreshToken->getSignature()}\":\"{$refreshToken->getSecretKey()}\"}'::jsonb " .
                " returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user)) {
                throw new \Exception("Запись в базу не удалась.");
            }

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
