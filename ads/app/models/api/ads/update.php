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
        // обновляет юзеров
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
                "isAccessToken" => [["access_token", $p["access_token"]]],
                "isNumeric" => [
                    ["drive_id", $p["drive_id"]],
                    ["transmission_id", $p["transmission_id"]],
                    ["body_id", $p["body_id"]],
                    ["mileage", $p["mileage"]],
                    ["fuel_id", $p["fuel_id"]],
                    ["power", $p["power"]],
                    ["wheel_id", $p["wheel_id"]],
                    ["document_id", $p["document_id"]],
                    ["state_id", $p["state_id"]],
                    ["exchange_id", $p["exchange_id"]],
                    ["ad_id", $p["ad_id"]],
                ],
                "between" => [
                    ["mileage", $p["mileage"], ["min" => 0, "max" => 99999999]],
                    ["power", $p["power"], ["min" => 1, "max" => 9999]],
                ],
                "strLen" => [
                    ["description", $p["description"], ["min" => 0, "max" => 1600]],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $profileID = $tokenStructur->getUserID();

            // пишем в базу
            // формируем запрос
            $qSet = "";
            $qSet = $qSet . (empty($p["title"]) ? "" : " title='{$p["title"]}',");
            $qSet = $qSet . (empty($p["description"]) ? "" : " description='{$p["description"]}',");
            $qSet = $qSet . (empty($p["city_id"]) ? "" : " city_id='{$p["city_id"]}',");
            $qSet = $qSet . (empty($p["model_id"]) ? "" : " model_id='{$p["model_id"]}',");
            if (!empty($p["main_photo"])) {
                $qSet = $qSet . ($p["main_photo"] == "null" ? " main_photo=null," : " main_photo='{$p["main_photo"]}',");
            }
            $qSet = (empty($qSet) ? "" : substr($qSet, 0, -1));
            if (empty($qSet)) {
                throw new \Exception("Запрос пустой не имеет параметров.");
            }
            $q = "update posts set {$qSet} where user_id={$tokenStructur->getUserID()} and post_id={$postID} RETURNING *;";
            $db = $this->container['db'];
            $post = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            if (empty($post["user_id"])) {
                throw new \Exception("Такой пост не существует.");
            }
            return ["status" => "ok",
                "data" => ["post" => $post],
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
