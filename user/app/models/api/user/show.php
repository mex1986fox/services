<?php
namespace App\Models\Api\User;

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
            $userID = empty($p["user_id"]) ? "" : $p["user_id"];
            $login = empty($p["login"]) ? "" : $p["login"];
            $name = empty($p["name"]) ? "" : $p["name"];
            $surname = empty($p["surname"]) ? "" : $p["surname"];
            $countriesID = empty($p["countries_id"]) ? "" : array_diff($p["countries_id"], array(''));
            $subjectsID = empty($p["subjects_id"]) ? "" : array_diff($p["subjects_id"], array(''));
            $citiesID = empty($p["cities_id"]) ? "" : array_diff($p["cities_id"], array(''));
            // проверяем параметры

            // строим запрос
            $qWhere = "";
            $qWhere = $qWhere . (empty($userID) ? "" : " user_id=" . $userID . " and ");
            $qWhere = $qWhere . (empty($login) ? "" : " users.login ILIKE '%" . $login . "%' and ");
            $qWhere = $qWhere . (empty($name) ? "" : " users.name ILIKE '%" . $name . "%' and ");
            $qWhere = $qWhere . (empty($surname) ? "" : " users.surname ILIKE '%" . $surname . "%' and ");

            //для местоположения
            if (!empty($citiesID) || !empty($subjectsID) || !empty($countriesID)) {
                $qWhere = $qWhere . " (";
            } //  для страны
            $qWhere = $qWhere . (empty($countriesID) ? "" : "countries.country_id in (" . implode(', ', $countriesID) . ") or ");
            //  для региона
            $qWhere = $qWhere . (empty($subjectsID) ? "" : "subjects.subject_id in (" . implode(', ', $subjectsID) . ") or ");
            //  для города
            $qWhere = $qWhere . (empty($citiesID) ? "" : "cities.city_id in (" . implode(', ', $citiesID) . ") or ");
            if (!empty($citiesID) || !empty($subjectsID) || !empty($countriesID)) {
                $qWhere = rtrim($qWhere, ' or ') . ") and ";
            }

            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;
            // пишем в базу
            $db = $this->container['db'];
            $q =
                " select user_id, login, avatar, users.name, surname, birthdate, phone, email,  " .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country from users " .
                " LEFT JOIN cities ON cities.city_id = users.city_id " .
                " LEFT JOIN subjects ON subjects.subject_id = cities.subject_id " .
                " LEFT JOIN countries ON countries.country_id = cities.country_id " .
                $qWhere;
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
