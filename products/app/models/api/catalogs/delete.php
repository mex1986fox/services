<?php
namespace App\Models\Api\Catalogs;

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
        // обновляет юзеров
        try {

            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];

            // проверяем обязательные параметры
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
            }
            if (empty($p["catalog_id"])) {
                $exceptions["catalog_id"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            // проверяем параметры
            $accessToken = $p["access_token"];
            $catalogID = $p["catalog_id"];
            if (!is_numeric($catalogID)) {
                $exceptions["catalog_id"] = "Не соответствует типу integer.";
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);

            $valid = $this->container['validators'];
            $tokenSKey = $this->container['services']['token']['key_access_token'];
            $vToken = $valid->TokenValidator;
            $vToken->setKey($tokenSKey);
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["access_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $profileID = $tokenStructur->getUserID();

            // пишем в базу
            // удаляем лайки
            // $q = "delete from votes where user_id={$profileID} and catalog_id={$catalogID}";
            // $db = $this->container['db'];
            // $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            // удаляем посты
            $q = "delete from catalogs where user_id={$profileID} and catalog_id={$catalogID}";
            $db = $this->container['db'];
            $result = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            if ($result === false) {
                throw new \Exception("Удаление без результата.");
            }
            // удаляем фотографии

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
