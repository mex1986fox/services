<?php
namespace App\Models\Api\Token;

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
                throw new \Exception("Ошибки в параметрах.");
            }
            $userID = $p["user_id"];

            // проверяем параметры
            $valid = $this->container['validators'];
            $vDigits = $valid->Digits;
            if (!$vDigits->isValid($userID)) {
                $exceptions["user_id"] = "Не соответствет заданному типу.";
                throw new \Exception("Ошибки в параметрах.");
            }

            $db = $this->container['db'];
            $q = "update tokens set access_tokens = '[]'::jsonb where user_id = {$userID} returning *;";
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
