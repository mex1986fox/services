<?php
namespace App\Models\Api\Ads;

class Show
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
        // показывает блоги в системе
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            $limit = 5;
            $qSort = "";
            $qWhere = "";
            //для пагинации
            $sortID = empty($p["sort_id"]) ? 0 : $p["sort_id"];
            $page = empty($p["page"]) ? 1 : $p["page"];

            // проверяем параметры
            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем только заполненные параметры
            if (!$vMethods->isValidFilled([
                "emptyParamsFilled" => [
                    ["ad_id", $p], ["ads_id", $p],
                    ["sort_id", $p], ["page", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],
                    ["models_id", $p], ["brands_id", $p], ["types_id", $p],
                    ["drive_id", $p], ["transmission_id", $p], ["body_id", $p], ["mileage", $p], ["mileage2", $p],
                    ["fuel_id", $p], ["power", $p], ["power2", $p], ["volume", $p], ["volume2", $p],
                    ["wheel_id", $p], ["document_id", $p], ["state_id", $p], ["exchange_id", $p],
                ],
                "isInt" => [
                    ["ad_id", $p],
                    ["sort_id", $p], ["page", $p],
                    ["drive_id", $p], ["transmission_id", $p], ["body_id", $p], ["mileage", $p], ["mileage2", $p],
                    ["fuel_id", $p], ["power", $p], ["power2", $p],
                    ["wheel_id", $p], ["document_id", $p], ["state_id", $p], ["exchange_id", $p],
                ],
                "isArray" => [
                    ["ads_id", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],
                    ["models_id", $p], ["brands_id", $p], ["types_id", $p],
                ],
                "isFloat" => [
                    ["volume", $p], ["volume2", $p],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            //формируем условия сортировки
            $qSort .= $sortID == 0 ? "ads.date_create DESC, " : "";
            $qSort .= $sortID == 1 ? "ads.date_create DESC,  " : "";
            $qSort .= $sortID == 2 ? "ads.date_create ASC,  " : "";
            $qSort .= $sortID == 3 ? "cities.name ASC,  " : "";
            $qSort .= $sortID == 4 ? "cities.name DESC,  " : "";
            $qSort .= $sortID == 5 ? "models.name ASC,  " : "";
            $qSort .= $sortID == 6 ? "models.name DESC,  " : "";
            $qSort .= $sortID == 7 ? "ads.price DESC,  " : "";
            $qSort .= $sortID == 8 ? "ads.price ASC,  " : "";
            $qSort .= $sortID == 9 ? "ads.mileage DESC NULLS LAST, " : "";
            $qSort .= $sortID == 10 ? "ads.mileage ASC,  " : "";
            $qSort .= $sortID == 11 ? "ads.power DESC NULLS LAST, " : "";
            $qSort .= $sortID == 12 ? "ads.power ASC,  " : "";

            // строим запрос выборки
            $qWhere = $qWhere . (empty($p["ad_id"]) ? "" : "ads.ad_id=" . $p["ad_id"] . " and ");
            $qWhere = $qWhere . (empty($p["ads_id"]) ? "" : "ads.ad_id in (" . implode(', ', $p["ads_id"]) . ") and ");
            //для местоположения
            $qWhereLocat = "";
            $qWhereLocat .= empty($p["countries_id"]) ? "" : " countries.country_id in (" . implode(', ', $p["countries_id"]) . ") or ";
            $qWhereLocat .= empty($p["subjects_id"]) ? "" : " subjects.subject_id in (" . implode(', ', $p["subjects_id"]) . ") or ";
            $qWhereLocat .= empty($p["cities_id"]) ? "" : " cities.city_id in (" . implode(', ', $p["cities_id"]) . ") or ";
            if (!empty($qWhereLocat)) {
                $qWhere .= " (" . rtrim($qWhereLocat, ' or ') . ") and ";
            }
            //для транспорта
            $qWhereTrans = "";
            $qWhereTrans .= empty($p["models_id"]) ? "" : " models.model_id in (" . implode(', ', $p["models_id"]) . ") or ";
            $qWhereTrans .= empty($p["brands_id"]) ? "" : " brands.brand_id in (" . implode(', ', $p["brands_id"]) . ") or ";
            $qWhereTrans .= empty($p["types_id"]) ? "" : " types.type_id in (" . implode(', ', $p["types_id"]) . ") or ";
            if (!empty($qWhereTrans)) {
                $qWhere .= " (" . rtrim($qWhereTrans, ' or ') . ") and ";
            }
            //прочее
            $qWhere .= empty($p["drive_id"]) ? "" : " drives.drive_id in (" . implode(', ', $p["drive_id"]) . ") and ";
            $qWhere .= empty($p["transmission_id"]) ? "" : " transmissions.transmission_id in (" . implode(', ', $p["transmission_id"]) . ") and ";
            $qWhere .= empty($p["body_id"]) ? "" : " bodies.body_id in (" . implode(', ', $p["body_id"]) . ") and ";
            $qWhere .= empty($p["mileage"]) ? "" : " ads.mileage>={$p["mileage"]} and ";
            $qWhere .= empty($p["mileage2"]) ? "" : " ads.mileage<={$p["mileage2"]} and ";
            //двигатель
            $qWhere .= empty($p["fuel_id"]) ? "" : " fuels.fuel_id in (" . implode(', ', $p["fuel_id"]) . ") and ";
            $qWhere .= empty($p["power"]) ? "" : " ads.power>={$p["power"]} and ";
            $qWhere .= empty($p["power2"]) ? "" : " ads.power<={$p["power2"]} and ";
            $qWhere .= empty($p["volume"]) ? "" : " ads.volume>={$p["volume"]} and ";
            $qWhere .= empty($p["volume2"]) ? "" : " ads.volume<={$p["volume2"]} and ";

            $qWhere .= empty($wheelID) ? "" : " ads.wheel_id={$wheelID} and ";
            $qWhere .= empty($documentID) ? "" : " ads.document_id={$documentID} and ";
            $qWhere .= empty($stateID) ? "" : " ads.state_id in (" . implode(', ', $p["state_id"]) . ") and ";
            $qWhere .= empty($exchangeID) ? "" : " ads.exchange_id={$exchangeID} and ";

            $qWhere = $qWhere;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;
            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort . ($page > 1 ? " OFFSET " . ($page * $limit - $limit) : "");

            // пишем в базу
            $db = $this->container['db'];
            $q =
                " select ads.user_id as user_id, ads.ad_id as ad_id, ads.date_create, ads.DESCription, " .
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
                $qWhere . $qSort . " LIMIT " . $limit;
            $ads = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["page" => $page, "ads" => $ads],
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
