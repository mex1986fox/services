<?php
namespace App\Models\Api\Photos;

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
            if (!empty($p["users_id"])) {
                if (!is_array($p["users_id"])) {
                    $exceptions["users_id"] = "Должен быть массивом.";
                    throw new \Exception("Ошибки в параметрах.");
                }
            }
            $usersID = empty($p["users_id"]) ? "" : implode(",", $p["users_id"]);
            // строим запрос
            $qWhere = "";
            $qWhere = $qWhere . (empty($usersID) ? "" : " user_id in ({$usersID}) ");
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;

            // пишем в базу
            $db = $this->container['db'];
            $q = "select albums, user_id from photos " . $qWhere;
            $albums = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $files = array();
            foreach ($albums as $key => $album) {
                $alb=json_decode($album["albums"],1);
                $alb["user_id"]=$album["user_id"];
                $files[] = $alb;
            }
            return ["status" => "ok",
                "data" => $files,
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
