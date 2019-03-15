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
        // удаляет токен по id пользователя
        // доступен только для доверенных сервисов

        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];

            // проверяем параметры
            // указан ли токен
            if (empty($p["user_id"])) {
                $exceptions["user_id"] = "Не указан.";
            }
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }
            $userID = $p["user_id"];
            $accessToken = $p["access_token"];
            // проверяем параметры
            $valid = $this->container['validators'];
            $vDigits = $valid->Digits;
            if (!$vDigits->isValid($userID)) {
                $exceptions["user_id"] = "Не соответствет заданному типу.";
                throw new \Exception("Ошибки в параметрах.");
            }
            // записываем токен в структуру
            // для проверки
            $token = new TokenStructur();
            $token->setToken($accessToken);

            // вставит новую строку или обновит существующую
            $db = $this->container['db'];
            $q =
                " insert into tokens (user_id, access_tokens) " .
                " values({$userID},'[\"{$accessToken}\"]'::jsonb) " .
                " on conflict (user_id) do " .
                " update set access_tokens = '[\"{$accessToken}\"]'::jsonb returning *; ";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                throw new \Exception("Запись в базу не удалась.");
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
