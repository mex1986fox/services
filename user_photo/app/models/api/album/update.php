<?php
namespace App\Models\Api\User;

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
        // обновляет юзеров
        try {

            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }

            $accessToken = $p["access_token"];
            $tokenStructur = new TokenStructur();
            $tokenStructur->setToken($accessToken);

            // проверяем параметры
            $valid = $this->container['validators'];
            $vToken = $valid->TokenValidator;
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["access_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $vStLen = $valid->StringLength;
            $vStLen->setMin(1);
            $vStLen->setMax(64);
            if (!empty($p["name"])) {
                if (!$vStLen->isValid($p["name"])) {
                    $exceptions["name"] = "От 1 до 64 символов.";
                }
            }
            if (!empty($p["surname"])) {
                if (!$vStLen->isValid($p["surname"])) {
                    $exceptions["surname"] = "От 1 до 64 символов.";
                }
            }
            if (!empty($p["birthdate"])) {
                $vDate = $valid->Date;
                if (!$vDate->isValid($p["birthdate"])) {
                    $exceptions["birthdate"] = "Не соответствует типу date.";
                }
            }
            if (!empty($p["email"])) {
                $vEmail = $valid->EmailAddress;
                if (!$vEmail->isValid($p["email"])) {
                    $exceptions["email"] = "Не соответствует типу email.";
                }
            }
            if (!empty($p["city_id"])) {
                if (!is_numeric($p["city_id"])) {
                    $exceptions["city_id"] = "Не соответствует типу integer.";
                }
            }
            if (!empty($p["phone"])) {
                $vStLen->setMin(11);
                $vStLen->setMax(11);
                if (!$vStLen->isValid($p["phone"])) {
                    $exceptions["phone"] = "11 символов.";
                }
                if (!is_numeric($p["phone"])) {
                    $exceptions["phone"] = "Не соответствует типу integer.";
                }
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            // пишем в базу
            // формируем запрос
            $qSet = "";
            $qSet = $qSet . (empty($p["name"]) ? "" : " name='{$p["name"]}',");
            $qSet = $qSet . (empty($p["surname"]) ? "" : " surname='{$p["surname"]}',");
            $qSet = $qSet . (empty($p["birthdate"]) ? "" : " birthdate='{$p["birthdate"]}',");
            $qSet = $qSet . (empty($p["city_id"]) ? "" : " city_id={$p["city_id"]},");
            $qSet = $qSet . (empty($p["phone"]) ? "" : " phone='{$p["phone"]}',");
            $qSet = $qSet . (empty($p["email"]) ? "" : " email='{$p["email"]}',");
            $qSet = (empty($qSet) ? "" : substr($qSet, 0, -1));
            if (!empty($qSet)) {
                $q = "update users set {$qSet} where id={$tokenStructur->getUserID()} RETURNING *;";
                $db = $this->container['db'];
                $user = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
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
