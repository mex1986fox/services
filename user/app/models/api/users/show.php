<?php
namespace App\Models\Api\Users;

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
        // показывает юзеров в системе
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            $limit = 3;
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
                    ["sort_id", $p], ["page", $p],
                    ["login", $p], ["name", $p], ["surname", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],

                ],
                "isInt" => [
                    ["ad_id", $p],
                    ["sort_id", $p], ["page", $p],
                ],
                "isArray" => [
                    ["users_id", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            //формируем условия сортировки
            $qSort .= $sortID == 0 ? " user_id DESC, " : "";
            $qSort .= $sortID == 1 ? " user_id DESC, " : "";
            $qSort .= $sortID == 2 ? " user_id ASC, " : "";
            $qSort .= $sortID == 3 ? " users.name DESC NULLS LAST, " : "";
            $qSort .= $sortID == 4 ? " users.name ASC, " : "";
            $qSort .= $sortID == 5 ? " users.surname DESC NULLS LAST, " : "";
            $qSort .= $sortID == 6 ? " users.surname ASC, " : "";
            $qSort .= $sortID == 7 ? " users.login DESC NULLS LAST, " : "";
            $qSort .= $sortID == 8 ? " users.login ASC, " : "";

            // строим запрос выборки
            $qWhere = $qWhere . (empty($p["user_id"]) ? "" : " user_id=" . $p["user_id"] . " and ");
            $qWhere = $qWhere . (empty($p["login"]) ? "" : " users.login ILIKE '%" . $p["login"] . "%' and ");
            $qWhere = $qWhere . (empty($p["name"]) ? "" : " users.name ILIKE '%" . $p["name"] . "%' and ");
            $qWhere = $qWhere . (empty($p["surname"]) ? "" : " users.surname ILIKE '%" . $p["surname"] . "%' and ");

            //для местоположения
            $qWhereLocat = "";
            $qWhereLocat .= empty($p["countries_id"]) ? "" : " countries.country_id in (" . implode(', ', $p["countries_id"]) . ") or ";
            $qWhereLocat .= empty($p["subjects_id"]) ? "" : " subjects.subject_id in (" . implode(', ', $p["subjects_id"]) . ") or ";
            $qWhereLocat .= empty($p["cities_id"]) ? "" : " cities.city_id in (" . implode(', ', $p["cities_id"]) . ") or ";
            if (!empty($qWhereLocat)) {
                $qWhere .= " (" . rtrim($qWhereLocat, ' or ') . ") and ";
            }

            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;

            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort . ($page > 1 ? " OFFSET " . ($page * $limit - $limit) : "");

            // пишем в базу
            $db = $this->container['db'];
            $q =
                " select user_id, login, avatar, users.name, surname, birthdate, email, " .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country from users " .
                " LEFT JOIN cities ON cities.city_id = users.city_id " .
                " LEFT JOIN subjects ON subjects.subject_id = cities.subject_id " .
                " LEFT JOIN countries ON countries.country_id = cities.country_id " .
                $qWhere . $qSort . " LIMIT " . $limit;
            $users = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["page" => $page, "users" => $users],
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
