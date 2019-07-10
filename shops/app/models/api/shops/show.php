<?php
namespace App\Models\Api\Shops;

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
                    ["user_id", $p], ["users_id", $p],
                    ["shop_id", $p], ["shops_id", $p],
                    ["sort_id", $p], ["page", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],
                ],
                "isInt" => [
                    ["user_id", $p],
                    ["shop_id", $p],
                    ["sort_id", $p], ["page", $p],
                ],
                "isArray" => [
                    ["users_id", $p],
                    ["shops_id", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],
                ]
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            //формируем условия сортировки
            $qSort .= $sortID == 0 ? "shops.date_create DESC, " : "";
            $qSort .= $sortID == 1 ? "shops.date_create DESC,  " : "";
            $qSort .= $sortID == 2 ? "shops.date_create ASC,  " : "";
            $qSort .= $sortID == 3 ? "cities.name ASC,  " : "";
            $qSort .= $sortID == 4 ? "cities.name DESC,  " : "";
            $qSort .= $sortID == 5 ? "models.name ASC,  " : "";
            $qSort .= $sortID == 6 ? "models.name DESC,  " : "";

            // строим запрос выборки
            $qWhere = $qWhere . (empty($p["user_id"]) ? "" : " shops.user_id=" . $p["user_id"] . " and ");
            $qWhere = $qWhere . (empty($p["users_id"]) ? "" : "shops.user_id in (" . implode(', ', $p["users_id"]) . ") and ");
            $qWhere = $qWhere . (empty($p["shop_id"]) ? "" : "shops.shop_id=" . $p["shop_id"] . " and ");
            $qWhere = $qWhere . (empty($p["shops_id"]) ? "" : "shops.shop_id in (" . implode(', ', $p["shops_id"]) . ") and ");
            //для местоположения
            $qWhereLocat = "";
            $qWhereLocat .= empty($p["countries_id"]) ? "" : " countries.country_id in (" . implode(', ', $p["countries_id"]) . ") or ";
            $qWhereLocat .= empty($p["subjects_id"]) ? "" : " subjects.subject_id in (" . implode(', ', $p["subjects_id"]) . ") or ";
            $qWhereLocat .= empty($p["cities_id"]) ? "" : " cities.city_id in (" . implode(', ', $p["cities_id"]) . ") or ";
            if (!empty($qWhereLocat)) {
                $qWhere .= " (" . rtrim($qWhereLocat, ' or ') . ") and ";
            }

            $qWhere = $qWhere;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;
            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort . ($page > 1 ? " OFFSET " . ($page * $limit - $limit) : "");

            // пишем в базу
            $db = $this->container['db'];
            $q =
                " select shops.user_id as user_id, shops.shop_id as shop_id, shops.date_create, shops.description, " .
                " shops.main_photo, shops.title, " .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country " .
                " from shops " .
                " LEFT JOIN cities ON cities.city_id = shops.city_id " .
                " LEFT JOIN subjects ON subjects.subject_id = cities.subject_id " .
                " LEFT JOIN countries ON countries.country_id = cities.country_id " .
                $qWhere . $qSort . " LIMIT " . $limit;
            $shops = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["page" => $page, "shops" => $shops],
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
