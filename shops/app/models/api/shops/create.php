<?php
namespace App\Models\Api\Shops;

use \App\Services\Structur\TokenStructur;

class Create
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
        // создает новых объявления
        try {
            $p = $this->request->getQueryParams();
            $exceptions = [];

            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем обязательные для ввода
            if (!$vMethods->isValid([
                "emptyParams" => [
                    ["access_token", $p],
                    ["city_id", $p],
                    ["title", $p],
                    ['description', $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            $accessToken = $p["access_token"];
            $cityID = $p["city_id"];
            $title = $p["title"];
            $description = $p["description"];
            // проверяем параметры
            if (!$vMethods->isValid([
                "isAccessToken" => [["access_token", $accessToken]],
                "isNumeric" => [
                    ["city_id", $cityID],
                ],
                "strLen" => [
                    ["title", $title, ["min" => 1, "max" => 70]],
                    ["description", $description, ["min" => 1, "max" => 1600]],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $profileID = $tokenStructur->getUserID();

            // пишем в базу
            $db = $this->container['db'];
            $q = "insert into shops
                    (user_id, city_id, description, title)
                values
                    ({$profileID},{$cityID},'{$description}','{$title}')
                returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $shop = $sth->fetch();
            if (!isset($shop["shop_id"])) {
                throw new \Exception("Запись в базу не удалась.");
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
            $shopSelect = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            return ["status" => "ok",
                "data" => ["shop" => $shopSelect],
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
