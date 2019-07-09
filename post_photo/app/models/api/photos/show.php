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
            $limit = 200;
            $p = $this->request->getQueryParams();
            $exceptions = [];
            if (!empty($p["users_id"])) {
                if (!is_array($p["users_id"])) {
                    $exceptions["users_id"] = "Должен быть массивом.";
                    throw new \Exception("Ошибки в параметрах.");
                }
            }
            if (!empty($p["entities_id"])) {
                if (!is_array($p["entities_id"])) {
                    $exceptions["entities_id"] = "Должен быть массивом.";
                    throw new \Exception("Ошибки в параметрах.");
                }
            }
            $usersID = empty($p["users_id"]) ? "" : implode(",", $p["users_id"]);
            $entitiesID = empty($p["entities_id"]) ? "" : implode(",", $p["entities_id"]);
            // строим запрос
            $qWhere = "";
            $qWhere = $qWhere . (empty($usersID) ? "" : " user_id in ({$usersID}) and ");
            $qWhere = $qWhere . (empty($entitiesID) ? "" : " entity_id in ({$entitiesID}) and ");

            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;

            // пишем в базу
            $db = $this->container['db'];
            $q = "select * from photos " . $qWhere . " LIMIT " . $limit;
            $albums = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            foreach ($albums as $key => $album) {
                $albums[$key]["mini"] = json_decode($album["mini"], true);
                $albums[$key]["origin"] = json_decode($album["origin"], true);
            }
            return ["status" => "ok",
                "data" => ["albums" => $albums],
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
