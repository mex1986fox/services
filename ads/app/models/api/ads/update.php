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
                // "emptyParamsFilled" => [
                //     ["city_id", $p],
                //     ["model_id", $p],
                // ],
                "isAccessToken" => [["access_token", $p]],
                "isNumeric" => [
                    ["city_id", $p],
                    ["model_id", $p],
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
                "isFloat" => [
                    ["volume", $p],
                ],
                "uri" => [
                    ["main_photo", $p],
                ],
                "strLen" => [
                    ["description", $p, ["min" => 0, "max" => 1600]],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($p["access_token"]);
            $profileID = $tokenStructur->getUserID();

            // переводим в null
            $nullPars = ["drive_id", "transmission_id", "body_id", "mileage", "fuel_id", "power", "volume",
                "wheel_id", "document_id", "state_id", "exchange_id", "description"];
            foreach ($nullPars as $key => $value) {
                if ($p[$value] == 0 && $p[$value] == '0') {
                    $p[$value] = 'null';
                }
                if ($p[$value] === "") {
                    $p[$value] = ' ';
                }
            }
            // формируем запрос
            $qSet = "";
            // $qSet .= (empty($p["city_id"]) ? "" : " city_id={$p["city_id"]},");
            // $qSet .= (empty($p["model_id"]) ? "" : " model_id={$p["model_id"]},");
            $qSet .= (empty($p["drive_id"]) ? "" : " drive_id={$p["drive_id"]},");
            $qSet .= (empty($p["transmission_id"]) ? "" : " transmission_id={$p["transmission_id"]},");
            $qSet .= (empty($p["body_id"]) ? "" : " body_id={$p["body_id"]},");
            $qSet .= (empty($p["mileage"]) ? "" : " mileage={$p["mileage"]},");
            $qSet .= (empty($p["fuel_id"]) ? "" : " fuel_id={$p["fuel_id"]},");
            $qSet .= (empty($p["power"]) ? "" : " power={$p["power"]},");
            $qSet .= (empty($p["volume"]) ? "" : " volume={$p["volume"]},");
            $qSet .= (empty($p["wheel_id"]) ? "" : " wheel_id={$p["wheel_id"]},");
            $qSet .= (empty($p["document_id"]) ? "" : " document_id={$p["document_id"]},");
            $qSet .= (empty($p["state_id"]) ? "" : " state_id={$p["state_id"]},");
            $qSet .= (empty($p["exchange_id"]) ? "" : " exchange_id={$p["exchange_id"]},");
            $qSet .= (empty($p["description"]) ? "" : " description='{$p["description"]}',");

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
            $q =
                " select ads.user_id as user_id, ads.ad_id as ad_id, ads.date_create, ads.description, " .
                " ads.main_photo, ads.year as year, price::numeric(10,0) as price, " .
                " mileage, power, volume, wheel_id, document_id, state_id, exchange_id, " .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country, " .
                " models.model_id, models.name as model, brands.brand_id, brands.name as brand, " .
                " types.type_id, types.name as type,  " .
                " votes.likes as likes, votes.dislikes as dislikes, " .
                " drives.name as drive, transmissions.name as transmission, bodies.name as body, fuels.name as fuel, " .
                " drives.drive_id as drive_id, transmissions.transmission_id as transmission_id, bodies.body_id as body_id, fuels.fuel_id as fuel_id " .
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
