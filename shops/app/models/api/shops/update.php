<?php
namespace App\Models\Api\Shops;

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
                    ["shop_id", $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            // проверяем только заполненные параметры
            if (!$vMethods->isValidFilled([
                "emptyParamsFilled" => [
                    ["shop_id", $p],
                    ["main_photo", $p],
                    ["title", $p],
                ],
                "isAccessToken" => [["access_token", $p]],
                "isNumeric" => [
                    ["shop_id", $p],
                ],
                "uri" => [
                    ["main_photo", $p],
                ],
                "strLen" => [
                    ["title", $p, ["min" => 1, "max" => 70]],
                    ["description", $p, ["min" => 0, "max" => 1600]],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($p["access_token"]);
            $profileID = $tokenStructur->getUserID();

            // переводим в null
            $nullPars = ["description"];
            foreach ($nullPars as $key => $value) {
                if (isset($p[$value]) && $p[$value] == 0 && $p[$value] == '0') {
                    $p[$value] = 'null';
                }
                if (isset($p[$value]) && $p[$value] === "") {
                    $p[$value] = ' ';
                }
            }
            // формируем запрос
            $qSet = "";
            // $qSet .= (empty($p["city_id"]) ? "" : " city_id={$p["city_id"]},");
            $qSet .= (empty($p["title"]) ? "" : " title='{$p["title"]}',");
            $qSet .= (empty($p["description"]) ? "" : " description='{$p["description"]}',");

            if (!empty($p["main_photo"])) {
                $qSet = $qSet . ($p["main_photo"] == "null" ? " main_photo=null," : " main_photo='{$p["main_photo"]}',");
            }
            $qSet = (empty($qSet) ? "" : substr($qSet, 0, -1));
            if (empty($qSet)) {
                throw new \Exception("Запрос пустой не имеет параметров.");
            }

            // пишем в базу
            $q = "update shops set {$qSet} where user_id={$profileID} and shop_id={$p["shop_id"]} RETURNING *;";
            $db = $this->container['db'];
            $shop = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            if (empty($shop["user_id"])) {
                throw new \Exception("Такое объявление не существует.");
            }
            $q =
                " select shops.shop_id as shop_id, shops.user_id as user_id, shops.date_create, shops.description, " .
                " shops.main_photo, shops.title as title, " .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country " .
                " from shops " .
                " LEFT JOIN cities ON cities.city_id = shops.city_id " .
                " LEFT JOIN subjects ON subjects.subject_id = cities.subject_id " .
                " LEFT JOIN countries ON countries.country_id = cities.country_id " .
                " WHERE shops.user_id={$profileID} and shops.shop_id={$shop["shop_id"]}";
            $adSelect = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            return ["status" => "ok",
                "data" => ["ad" => $adSelect],
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
