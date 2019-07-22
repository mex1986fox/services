<?php
namespace App\Models\Api\Products;

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
                    ["catalog_id", $p],
                    ["page", $p],
                ],
                "isInt" => [
                    ["user_id", $p],
                    ["shop_id", $p],
                    ["catalog_id", $p],
                    ["page", $p],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            // строим запрос выборки
            $qWhere = $qWhere . (empty($p["user_id"]) ? "" : " products.user_id=" . $p["user_id"] . " and ");
            $qWhere = $qWhere . (empty($p["shop_id"]) ? "" : "products.shop_id=" . $p["shop_id"] . " and ");
            $qWhere = $qWhere . (empty($p["catalog_id"]) ? "" : "products.catalog_id=" . $p["catalog_id"] . " and ");

            $qWhere = $qWhere;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;
            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort . ($page > 1 ? " OFFSET " . ($page * $limit - $limit) : "");

            // пишем в базу
            $db = $this->container['db'];
            $q =
                " select products.user_id as user_id, products.shop_id as shop_id, products.catalog_id as catalog_id, " .
                " products.product_id as product_id, products.date_create, products.description, " .
                " models.model_id, models.name as model, brands.brand_id, brands.name as brand, " .
                " types.type_id, types.name as type,  " .
                " products.main_photo, products.title " .
                " from products " .
                " LEFT JOIN models ON models.model_id = products.model_id " .
                " LEFT JOIN brands ON brands.brand_id = products.brand_id " .
                " LEFT JOIN types ON types.type_id = products.type_id " .
                $qWhere . $qSort . " LIMIT " . $limit;
            $products = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["page" => $page, "products" => $products],
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
