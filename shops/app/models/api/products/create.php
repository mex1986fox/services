<?php
namespace App\Models\Api\Products;

use \App\Services\Structur\TokenStructur;

class Create
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
        // создает новых объявления
        try {
            $p = $this->request->getQueryParams();
            $exceptions = [];

            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем обязательные для ввода
            if (!$vMethods->isValid([
                "emptyParams" => [
                    ["access_token", $p],
                    ["shop_id", $p],
                    ["catalog_id", $p],
                    ["title", $p],
                    ['description', $p],
                    ["price", $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            $accessToken = $p["access_token"];
            $shopID = $p["shop_id"];
            $catalogID = $p["catalog_id"];
            $title = $p["title"];
            $price = $p["price"];
            $description = $p["description"];
            // проверяем параметры
            if (!$vMethods->isValid([
                "isAccessToken" => [["access_token", $accessToken]],
                "isNumeric" => [
                    ["shop_id", $shopID],
                    ["catalog_id", $catalogID],
                    ["price", $price],
                ],
                "between" => [
                    ["price", $price, ["min" => 500, "max" => 999999999]],
                ],
                "strLen" => [
                    ["title", $title, ["min" => 1, "max" => 70]],
                    ["description", $description, ["min" => 1, "max" => 1600]],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $profileID = $tokenStructur->getUserID();
            
            // формируем под запросы 
            $subquery="";
            if (isset($p['type_id'])){
                $subquery= ",null, null, select type_id from types where type_id={$p['type_id']}";
            }
            if (isset($p['brand_id'])){
                $subquery= ",null, select brand_id, type_id from brands where brand_id={$p['brand_id']}";
            }
            if (isset($p['model_id'])){
                $subquery= "select model_id, brand_id, type_id from models where model_id={$p['model_id']}";
            }
            if($subquery==""){
                $subquery=",null, null, null";
            }

            // пишем в базу
            $db = $this->container['db'];
            $q = "insert into products
                    (user_id, shop_id, catalog_id, description, title, price)
                values
                    ({$profileID},{$shopID},{$catalogID},'{$description}','{$title}','{$price}')
                returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $product = $sth->fetch();
            if (!isset($product["product_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }

            return ["status" => "ok",
                "data" => ["product" => $product],
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
