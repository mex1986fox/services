<?php
namespace App\Models\Api\Photos;

use \App\Services\Structur\TokenStructur;

class CheckMain
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

            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем обязательные для ввода

            if (!$vMethods->isValid([
                "emptyParams" => [
                    ["access_token", $p],
                    ["entity_id", $p],
                    ["name_file", $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };
            //передаем в переменные
            $entityID = $p["entity_id"];
            $nameFile = $p["name_file"];
            $accessToken = $p["access_token"];

            // проверяем параметры
            if (!$vMethods->isValid([
                "isNumeric" => [["entity_id", $entityID]],
                "isAccessToken" => [["access_token", $accessToken]],
                "strLen" => [
                    ["name_file", $nameFile, ["min" => 13, "max" => 13]],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $userID = $tokenStructur->getUserID();

            $dbreqwests = $this->container['db-requests'];
            $dbrCMF = $dbreqwests->RequestCheckMainFile;
            $dbrCMFStatus = $dbrCMF->go($userID, $entityID, $nameFile);
            if ($dbrCMFStatus != true) {
                throw new \Exception($dbrCMFStatus);
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
