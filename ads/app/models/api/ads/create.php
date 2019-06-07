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
                "between" => [
                    ["price", $price, ["min" => 500, "max" => 999999999]],
                    ["year", $year, ["min" => 1936, "max" => date("Y")]],
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

            $q =
                " select ads.user_id as user_id, ads.ad_id as ad_id, ads.date_create, ads.description, " .
                " ads.main_photo, ads.year as year, price::numeric(10,0) as price, " .
                " mileage, power, volume, wheel_id, document_id, state_id, exchange_id, " .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country, " .
                " models.model_id, models.name as model, brands.brand_id, brands.name as brand, " .
                " types.type_id, types.name as type,  " .
                " votes.likes as likes, votes.dislikes as dislikes, " .
                " drives.name as drive, transmissions.name as transmission, bodies.name as body, fuels.name as fuel " .
                " from ads " .
                " LEFT JOIN cities ON cities.city_id = ads.city_id " .
                " LEFT JOIN subjects ON subjects.subject_id = cities.subject_id " .
                " LEFT JOIN countries ON countries.country_id = cities.country_id " .
                " LEFT JOIN models ON models.model_id = ads.model_id " .
                " LEFT JOIN brands ON brands.brand_id = models.brand_id " .
                " LEFT JOIN types ON types.type_id = models.type_id " .
                " LEFT JOIN votes ON votes.user_id = ads.user_id  and votes.ad_id = ads.ad_id " .
                " LEFT JOIN drives ON drives.drive_id = ads.drive_id " .
                " LEFT JOIN transmissions ON transmissions.transmission_id = ads.transmission_id " .
                " LEFT JOIN bodies ON bodies.body_id = ads.body_id " .
                " LEFT JOIN fuels ON fuels.fuel_id = ads.fuel_id " .
                " WHERE ads.user_id={$profileID} and ads.ad_id={$ad["ad_id"]}";
            $adSelect = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            return ["status" => "ok",
                "data" => $adSelect,
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
