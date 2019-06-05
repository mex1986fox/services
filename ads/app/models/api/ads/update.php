<?php
namespace App\Models\Api\Ads;

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
        // обновляет объявление
        try {

            $p = $this->request->getQueryParams();
            $exceptions = [];

            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем обязательные для ввода
            if (!$vMethods->isValid([
                "emptyParams" => [
                    ["access_token", $p],
                    ["ad_id", $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            // проверяем только заполненные параметры
            if (!$vMethods->isValidFilled([
                "isAccessToken" => [["access_token", $p]],
                "isNumeric" => [
                    ["drive_id", $p],
                    ["transmission_id", $p],
                    ["body_id", $p],
                    ["mileage", $p],
                    ["fuel_id", $p],
                    ["power", $p],
                    ["wheel_id", $p],
                    ["document_id", $p],
                    ["state_id", $p],
                    ["exchange_id", $p],
                    ["ad_id", $p],
                ],
                "between" => [
                    ["mileage", $p, ["min" => 0, "max" => 99999999]],
                    ["power", $p, ["min" => 1, "max" => 9999]],
                ],
                "toFloat" => [
                    ["volume", $p],
                ],
                "uri"=>[
                    ["main_photo", $p],
                ],
                "strLen" => [
                    ["description", $p, ["min" => 0, "max" => 1600]],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken( $p["access_token"]);
            $profileID = $tokenStructur->getUserID();

            // формируем запрос
            $qSet = "";
            $qSet = $qSet . (empty($p["drive_id"]) ? "" : " drive_id={$p["drive_id"]},");
            $qSet = $qSet . (empty($p["transmission_id"]) ? "" : " transmission_id={$p["transmission_id"]},");
            $qSet = $qSet . (empty($p["body_id"]) ? "" : " body_id={$p["body_id"]},");
            $qSet = $qSet . (empty($p["mileage"]) ? "" : " mileage={$p["mileage"]},");

            $qSet = $qSet . (empty($p["fuel_id"]) ? "" : " fuel_id={$p["fuel_id"]},");
            $qSet = $qSet . (empty($p["power"]) ? "" : " power={$p["power"]},");
            $qSet = $qSet . (empty($p["volume"]) ? "" : " volume={$p["volume"]},");

            $qSet = $qSet . (empty($p["wheel_id"]) ? "" : " wheel_id={$p["wheel_id"]},");
            $qSet = $qSet . (empty($p["document_id"]) ? "" : " document_id={$p["document_id"]},");
            $qSet = $qSet . (empty($p["state_id"]) ? "" : " state_id={$p["state_id"]},");
            $qSet = $qSet . (empty($p["exchange_id"]) ? "" : " exchange_id={$p["exchange_id"]},");
            $qSet = $qSet . (empty($p["description"]) ? "" : " description='{$p["description"]}',");

            if (!empty($p["main_photo"])) {
                $qSet = $qSet . ($p["main_photo"] == "null" ? " main_photo=null," : " main_photo='{$p["main_photo"]}',");
            }
            $qSet = (empty($qSet) ? "" : substr($qSet, 0, -1));
            if (empty($qSet)) {
                throw new \Exception("Запрос пустой не имеет параметров.");
            }

            // пишем в базу
            $q = "update ads set {$qSet} where user_id={$profileID} and ad_id={$p["ad_id"]} RETURNING *;";
            $db = $this->container['db'];
            $ad = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            if (empty($ad["user_id"])) {
                throw new \Exception("Такое объявление не существует.");
            }
            return ["status" => "ok",
                "data" => ["ad" => $ad],
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
