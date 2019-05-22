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

            $sortID = empty($p["sort_id"]) ? 0 : $p["sort_id"];
            //для пагинации от какого id юзера шагать при выборке
            $stepFrom = empty($p["step_from"]) ? "" : $p["step_from"];

            $userID = empty($p["user_id"]) ? "" : $p["user_id"];
            $login = empty($p["login"]) ? "" : $p["login"];
            $name = empty($p["name"]) ? "" : $p["name"];
            $surname = empty($p["surname"]) ? "" : $p["surname"];
            $countriesID = empty($p["countries_id"]) ? "" : array_diff($p["countries_id"], array(''));
            $subjectsID = empty($p["subjects_id"]) ? "" : array_diff($p["subjects_id"], array(''));
            $citiesID = empty($p["cities_id"]) ? "" : array_diff($p["cities_id"], array(''));

            // проверяем параметры
            // if (empty($sortID)) {
            //     $exceptions["sort_id"] = "Не указан.";
            //     throw new \Exception("Ошибки в параметрах.");
            // }
            // if (!is_numeric($sortID)) {
            //     $exceptions["sort_id"] = "Не соответствует типу integer.";
            //     throw new \Exception("Ошибки в параметрах.");
            // }
            // if ($sortID < 1 || $sortID > 2) {
            //     $exceptions["sort_id"] = "Не соответствует диапазону.";
            //     throw new \Exception("Ошибки в параметрах.");
            // }
            //формируем условия сортировки
            $qSort = "";
            $qWhere = "";
            $qWherePag = "";
            if (!empty($stepFrom)) {
                $db = $this->container['db'];
                $q = "select * from users where user_id=" . $stepFrom;
                $sfUser = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
                if (empty($sfUser)) {
                    $exceptions["step_from"] = "Не найден в системе.";
                    throw new \Exception("Ошибки в параметрах.");
                }
            }

            switch ($sortID) {
                case 0:
                    $qSort = $qSort . " user_id DESC, ";
                    //для пагинации
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " user_id<" . $sfUser["user_id"] . " and ");
                    break;
                case 1:
                    $qSort = $qSort . " user_id DESC, ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " user_id<" . $sfUser["user_id"] . " and ");
                    break;
                case 2:
                    $qSort = $qSort . " user_id ASC, ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " user_id>" . $sfUser["user_id"] . " and ");
                    break;
                case 3:
                    $qSort = $qSort . " users.name DESC, user_id DESC, ";
                    $qWhere = $qWhere . " users.name<>'' and ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " (users.name, user_id)<('" . $sfUser["name"] . "', " . $sfUser["user_id"] . ") and ");
                    break;
                case 4:
                    $qSort = $qSort . " users.name ASC, user_id ASC, ";
                    $qWhere = $qWhere . " users.name<>'' and ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " (users.name, user_id)>('" . $sfUser["name"] . "', " . $sfUser["user_id"] . ") and ");
                    break;
                case 5:
                    $qSort = $qSort . " users.surname DESC, user_id DESC, ";
                    $qWhere = $qWhere . " users.surname<>'' and ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " (users.surname, user_id)<('" . $sfUser["surname"] . "', " . $sfUser["user_id"] . ") and ");
                    break;
                case 6:
                    $qSort = $qSort . " users.surname ASC, user_id ASC, ";
                    $qWhere = $qWhere . " users.surname<>'' and ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " (users.surname, user_id)>('" . $sfUser["surname"] . "', " . $sfUser["user_id"] . ") and ");
                    break;
                case 7:
                    $qSort = $qSort . " users.login DESC, user_id DESC, ";
                    $qWhere = $qWhere . " users.login<>'' and ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " (users.login, user_id)<('" . $sfUser["login"] . "', " . $sfUser["user_id"] . ") and ");
                    break;
                case 8:
                    $qSort = $qSort . " users.login ASC, user_id ASC, ";
                    $qWhere = $qWhere . " users.login<>'' and ";
                    $qWherePag = $qWherePag . (empty($sfUser) ? "" : " (users.login, user_id)>('" . $sfUser["login"] . "', " . $sfUser["user_id"] . ") and ");
                    break;
            }

            // строим запрос выборки
            $qWhere = $qWhere . (empty($userID) ? "" : " user_id=" . $userID . " and ");

            $qWhere = $qWhere . (empty($login) ? "" : " users.login ILIKE '%" . $login . "%' and ");
            $qWhere = $qWhere . (empty($name) ? "" : " users.name ILIKE '%" . $name . "%' and ");
            $qWhere = $qWhere . (empty($surname) ? "" : " users.surname ILIKE '%" . $surname . "%' and ");

            //для местоположения
            if (!empty($citiesID) || !empty($subjectsID) || !empty($countriesID)) {
                $qWhere = $qWhere . " (";
            } //  для страны
            $qWhere = $qWhere . (empty($countriesID) ? "" : " countries.country_id in (" . implode(', ', $countriesID) . ") or ");
            //  для региона
            $qWhere = $qWhere . (empty($subjectsID) ? "" : " subjects.subject_id in (" . implode(', ', $subjectsID) . ") or ");
            //  для города
            $qWhere = $qWhere . (empty($citiesID) ? "" : " cities.city_id in (" . implode(', ', $citiesID) . ") or ");
            if (!empty($citiesID) || !empty($subjectsID) || !empty($countriesID)) {
                $qWhere = rtrim($qWhere, ' or ') . ") and ";
            }

            $qWhere = $qWhere . $qWherePag;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;

            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort;
            // пишем в базу
            $db = $this->container['db'];
            $q =
                " select user_id, login, avatar, users.name, surname, birthdate, email, " .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country from users " .
                " LEFT JOIN cities ON cities.city_id = users.city_id " .
                " LEFT JOIN subjects ON subjects.subject_id = cities.subject_id " .
                " LEFT JOIN countries ON countries.country_id = cities.country_id " .
                $qWhere . $qSort . " LIMIT 5";
            $users = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => $users,
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
