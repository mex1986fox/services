<?php
namespace App\Models\Api\Token;

use \App\Services\Structur\TokenStructur;

class Update
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
        // обновляет токены
        // используя refresh token
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            $refreshToken = $p["refresh_token"];

            // проверяем параметры
            // указан ли токен
            if (empty($refreshToken)) {
                $exceptions["refresh_token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $token = new TokenStructur($this->container);
            $token->setToken($refreshToken);
            //ищем ключ от токена
            $db = $this->container['db'];
            $q = "select * from tokens where user_id = '{$token->getUserID()}';";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();
            if (!isset($user["user_id"])) {
                throw new \Exception("Такой пользователь не зарегистрирован.");
            }
            $TokenKey = current(json_decode($user["refresh_tokens"], 1));
            if (empty($TokenKey)) {
                throw new \Exception("refresh_token отсутствует у пользователя.");
            }
            $TokenSignature = key(json_decode($user["refresh_tokens"], 1));
            if ($TokenSignature!=$token->getSignature()) {
                throw new \Exception("Сигнатура refresh_token не зарегистрированна в системе.");
            }
            // проверяем токен
            $valid = $this->container['validators'];
            $vToken = $valid->TokenValidator;
            $vToken->setKey($TokenKey);
            if (!$vToken->isValid($token)) {
                $exceptions["refresh_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (!$vToken->isValidLifeTime()) {
                $exceptions["refresh_token"] = "Истекло время жизни токена.";
                throw new \Exception("Ошибки в параметрах.");
            }


            // создаем токены доступа
            $accessToken = new TokenStructur($this->container);
            $accessToken->initAccessToken($user["user_id"]);

            $refreshToken = new TokenStructur($this->container);
            $refreshToken->initRefreshToken($user["user_id"]);

            $q = "update tokens
                    set access_tokens = '{\"{$accessToken->getSignature()}\":\"{$accessToken->getSecretKey()}\"}'::jsonb,
                        refresh_tokens = '{\"{$refreshToken->getSignature()}\":\"{$refreshToken->getSecretKey()}\"}'::jsonb
                    where user_id = '{$user["user_id"]}' returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }
           
            // отправить запросы на обновление токенов у микросервисов
            // $apiReqwests = $this->container['api-requests'];
            // $rCreateToken = $apiReqwests->RequestUpdateTokens;
            // $rCreateToken->go(["user_id" => $token->getUserID(), "access_token"=>$accessToken->getToken()]);


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
