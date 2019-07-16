<?php
namespace App\Models\Api\Catalogs;

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
                    ["title", $p],
                    ['description', $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            $accessToken = $p["access_token"];
            $shopID = $p["shop_id"];
            $title = $p["title"];
            $description = $p["description"];
            // проверяем параметры
            if (!$vMethods->isValid([
                "isAccessToken" => [["access_token", $accessToken]],
                "isNumeric" => [
                    ["shop_id", $shopID],
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

            // пишем в базу
            $db = $this->container['db'];
            $q = "insert into catalogs
                    (user_id, shop_id, description, title)
                values
                    ({$profileID},{$shopID},'{$description}','{$title}')
                returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $catalog = $sth->fetch();
            if (!isset($catalog["catalog_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }

            $q =
                " select catalogs.catalog_id as catalog_id, catalogs.user_id as user_id, catalogs.shop_id as shop_id, catalogs.date_create, catalogs.description, " .
                " catalogs.main_photo, catalogs.title as title " .
                " from catalogs " .
                " WHERE catalogs.user_id={$profileID} and catalogs.catalog_id={$catalog["catalog_id"]}";
            $catalogSelect = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            return ["status" => "ok",
                "data" => ["catalog" => $catalogSelect],
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
