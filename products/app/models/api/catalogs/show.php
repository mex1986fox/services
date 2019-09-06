<?php
namespace App\Models\Api\Catalogs;

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
            $limit = 100;
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
                    ["user_id", $p],
                    ["shop_id", $p],
                    ["page", $p],
                ],
                "isInt" => [
                    ["user_id", $p],
                    ["shop_id", $p],
                    ["page", $p],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            // строим запрос выборки
            $qWhere = $qWhere . (empty($p["user_id"]) ? "" : " catalogs.user_id=" . $p["user_id"] . " and ");
            $qWhere = $qWhere . (empty($p["shop_id"]) ? "" : "catalogs.shop_id=" . $p["shop_id"] . " and ");

            $qWhere = $qWhere;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;
            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort . ($page > 1 ? " OFFSET " . ($page * $limit - $limit) : "");

            // пишем в базу
            $db = $this->container['db'];
            $q =
                " select catalogs.user_id as user_id, catalogs.shop_id as shop_id, catalogs.catalog_id as catalog_id, catalogs.date_create, catalogs.description, " .
                " catalogs.main_photo, catalogs.title " .
                " from catalogs " .
                $qWhere . $qSort . " LIMIT " . $limit;
            $catalogs = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["page" => $page, "catalogs" => $catalogs],
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
