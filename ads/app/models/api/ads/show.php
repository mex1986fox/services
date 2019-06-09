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
            //для пагинации от какого id юзера шагать при выборке
            $sortID = empty($p["sort_id"]) ? 0 : $p["sort_id"];
            $countriesID = empty($p["countries_id"]) ? "" : array_diff($p["countries_id"], array(''));
            $subjectsID = empty($p["subjects_id"]) ? "" : array_diff($p["subjects_id"], array(''));
            $citiesID = empty($p["cities_id"]) ? "" : array_diff($p["cities_id"], array(''));
            $modelsID = empty($p["models_id"]) ? "" : array_diff($p["models_id"], array(''));
            $brandsID = empty($p["brands_id"]) ? "" : array_diff($p["brands_id"], array(''));
            $typesID = empty($p["types_id"]) ? "" : array_diff($p["types_id"], array(''));
            $bodyID = empty($p["body_id"]) ? "" : array_diff($p["body_id"], array(''));
            $transmissionID = empty($p["transmission_id"]) ? "" : array_diff($p["transmission_id"], array(''));
            $driveID = empty($p["drive_id"]) ? "" : array_diff($p["drive_id"], array(''));
            $mileage = empty($p["mileage"]) ? 0 : str_replace(" ", "", $p["mileage"]);
            $mileage2 = empty($p["mileage2"]) ? 0 : str_replace(" ", "", $p["mileage2"]);
            // проверяем параметры
            // проверяем параметры

            //формируем условия сортировки
            $qSort = "";
            $qWhere = "";
            $qWherePag = "";
            if (!empty($p["step_from"])) {
                $db = $this->container['db'];
                $q = "select ad_id, date_create, cities.name as city, models.name as model from ads"
                    . " LEFT JOIN cities ON cities.city_id = ads.city_id "
                    . " LEFT JOIN models ON models.model_id = ads.model_id "
                    . " where ad_id=" . $p["step_from"];
                $sfBlog = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
                if (empty($sfBlog)) {
                    $exceptions["step_from"] = "Не найден в системе.";
                    throw new \Exception("Ошибки в параметрах.");
                }
            }

            switch ($sortID) {
                case 0:
                    $qSort = $qSort . "ads.date_create DESC, ads.ad_id DESC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (ads.date_create, ads.ad_id)<('" . $sfBlog["date_create"] . "', " . $sfBlog["ad_id"] . ") and ");
                    break;
                case 1:
                    $qSort = $qSort . "ads.date_create DESC, ads.ad_id DESC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (ads.date_create, ads.ad_id)<('" . $sfBlog["date_create"] . "', " . $sfBlog["ad_id"] . ") and ");
                    break;
                case 2:
                    $qSort = $qSort . "ads.date_create ASC, ads.ad_id ASC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (ads.date_create, ads.ad_id)>('" . $sfBlog["date_create"] . "', " . $sfBlog["ad_id"] . ") and ");
                    break;
                case 3:
                    $qSort = $qSort . "cities.name ASC, ads.ad_id ASC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (cities.name, ads.ad_id)>('" . $sfBlog["city"] . "', " . $sfBlog["ad_id"] . ") and ");
                    break;
                case 4:
                    $qSort = $qSort . "cities.name desc, ads.ad_id desc, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (cities.name, ads.ad_id)<('" . $sfBlog["city"] . "', " . $sfBlog["ad_id"] . ") and ");
                    break;
                case 5:
                    $qSort = $qSort . "models.name ASC, ads.ad_id ASC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (models.name, ads.ad_id)>('" . $sfBlog["model"] . "', " . $sfBlog["ad_id"] . ") and ");
                    break;
                case 6:
                    $qSort = $qSort . "models.name desc, ads.ad_id desc, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (models.name, ads.ad_id)<('" . $sfBlog["model"] . "', " . $sfBlog["ad_id"] . ") and ");
                    break;
            }

            // строим запрос выборки
            $qWhere = $qWhere . (empty($p["ad_id"]) ? "" : " ad_id=" . $p["ad_id"] . " and ");

            //для местоположения
            $qWhereLocat = "";
            $qWhereLocat .= empty($countriesID) ? "" : " countries.country_id in (" . implode(', ', $p["countries_id"]) . ") or ";
            $qWhereLocat .= empty($subjectsID) ? "" : " subjects.subject_id in (" . implode(', ', $p["subjects_id"]) . ") or ";
            $qWhereLocat .= empty($citiesID) ? "" : " cities.city_id in (" . implode(', ', $p["cities_id"]) . ") or ";
            if (!empty($qWhereLocat)) {
                $qWhere .= " (" . rtrim($qWhereLocat, ' or ') . ") and ";
            }
            //для транспорта
            $qWhereTrans = "";
            $qWhereTrans .= empty($modelsID) ? "" : " models.model_id in (" . implode(', ', $p["models_id"]) . ") or ";
            $qWhereTrans .= empty($brandsID) ? "" : " brands.brand_id in (" . implode(', ', $p["brands_id"]) . ") or ";
            $qWhereTrans .= empty($typesID) ? "" : " types.type_id in (" . implode(', ', $p["types_id"]) . ") or ";
            if (!empty($qWhereTrans)) {
                $qWhere .= " (" . rtrim($qWhereTrans, ' or ') . ") and ";
            }
            //прочее
            $qWhere .= empty($driveID) ? "" : " drives.drive_id in (" . implode(', ', $p["drive_id"]) . ") and ";
            $qWhere .= empty($transmissionID) ? "" : " transmissions.transmission_id in (" . implode(', ', $p["transmission_id"]) . ") and ";
            $qWhere .= empty($bodyID) ? "" : " bodies.body_id in (" . implode(', ', $p["body_id"]) . ") and ";
            $qWhere .= empty($mileage) ? "" : " ads.mileage>={$mileage} and ";
            $qWhere .= empty($mileage2) ? "" : " ads.mileage<={$mileage2} and ";

            $qWhere = $qWhere . $qWherePag;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;
            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort;
            // пишем в базу
            $db = $this->container['db'];
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
                $qWhere . $qSort . " LIMIT 5";
            $ads = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["ads" => $ads],
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
