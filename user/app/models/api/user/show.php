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
            $userID = empty($p["user_id"])? "" : $p["user_id"];
           
            // строим запрос
            $qWhere = "";
            $qWhere = $qWhere . (empty($userID) ? "" : " id=" . $userID);
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;

            // пишем в базу
            $db = $this->container['db'];
            $q = "select id, login, name, surname, birthdate, city_id, phone, email from users " . $qWhere;
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
