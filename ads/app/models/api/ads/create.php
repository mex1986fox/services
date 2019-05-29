<?php
namespace App\Models\Api\Ads;

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
                    ["model_id", $p],
                    ["year", $p],
                    ['price', $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            $accessToken = $p["access_token"];
            $cityID = $p["city_id"];
            $modelID = $p["model_id"];
            $year = $p["year"];
            $price = preg_replace("/[^0-9]/", '', $p["price"]);
            // проверяем параметры
            if (!$vMethods->isValid([
                "isAccessToken" => [["access_token", $accessToken]],
                "isNumeric" => [
                    ["city_id", $cityID],
                    ["model_id", $modelID],
                    ["year", $year],
                    ["price", $price]],
                "strLen" => [
                    ["price", $year, ["min" => 3, "max" => 9]],
                    ["year", $year, ["min" => 4, "max" => 4]],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $profileID = $tokenStructur->getUserID();

            // пишем в базу
            $db = $this->container['db'];
            $q = "insert into ads
                    (user_id, city_id, model_id, price, year)
                values
                    ({$profileID},{$cityID},{$modelID},{$price},{$year})
                returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $ad = $sth->fetch();

            if (!isset($ad["ad_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }
            return ["status" => "ok",
                "data" => $ad,
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
