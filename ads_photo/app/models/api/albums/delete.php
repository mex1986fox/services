<?php
namespace App\Models\Api\Albums;

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
        // удаляет альбом целиком
        try {

            $p = $this->request->getQueryParams();
            $exceptions = [];

            // проверяем обязательные
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
            }
            if (empty($p["entity_id"])) {
                $exceptions["entity_id"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            // передаем параметры в переменные
            $entityID = $p["entity_id"];
            $accessToken = $p["access_token"];

            // проверяем параметры
            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $valid = $this->container['validators'];
            $tokenSKey = $this->container['services']['token']['key_access_token'];
            $vToken = $valid->TokenValidator;
            $vToken->setKey($tokenSKey);
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["access_token"] = "Не действителен.";
            }
            if (!is_numeric($p["entity_id"])) {
                $exceptions["entity_id"] = "Не соответствует типу integer.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            //вынимаем из токена id юзера
            $userID = $tokenStructur->getUserID();

            // удалим альбом в базе и дерриктории
            $dbreq = $this->container['db-requests'];
            $dbrDA = $dbreq->RequestDeleteAlbum;
            $dbrDAStatus = $dbrDA->go($userID, $entityID);
            if ($dbrDAStatus != true) {
                throw new \Exception($dbrDAStatus);
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
