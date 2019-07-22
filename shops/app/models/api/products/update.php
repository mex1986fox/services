<?php
namespace App\Models\Api\Products;

use \App\Services\Structur\TokenStructur;

class Update
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
        // обновляет объявление
        try {

            $p = $this->request->getQueryParams();
            $exceptions = [];

            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем обязательные для ввода
            if (!$vMethods->isValid([
                "emptyParams" => [
                    ["access_token", $p],
                    ["catalog_id", $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            // проверяем только заполненные параметры
            if (!$vMethods->isValidFilled([
                "emptyParamsFilled" => [
                    ["catalog_id", $p],
                    ["main_photo", $p],
                    ["title", $p],
                ],
                "isAccessToken" => [["access_token", $p]],
                "isNumeric" => [
                    ["catalog_id", $p],
                ],
                "uri" => [
                    ["main_photo", $p],
                ],
                "strLen" => [
                    ["title", $p, ["min" => 1, "max" => 70]],
                    ["description", $p, ["min" => 0, "max" => 1600]],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($p["access_token"]);
            $profileID = $tokenStructur->getUserID();

            // переводим в null
            $nullPars = ["description"];
            foreach ($nullPars as $key => $value) {
                if (isset($p[$value]) && $p[$value] == 0 && $p[$value] == '0') {
                    $p[$value] = 'null';
                }
                if (isset($p[$value]) && $p[$value] === "") {
                    $p[$value] = ' ';
                }
            }
            // формируем запрос
            $qSet = "";
            // $qSet .= (empty($p["city_id"]) ? "" : " city_id={$p["city_id"]},");
            $qSet .= (empty($p["title"]) ? "" : " title='{$p["title"]}',");
            $qSet .= (empty($p["description"]) ? "" : " description='{$p["description"]}',");

            if (!empty($p["main_photo"])) {
                $qSet = $qSet . ($p["main_photo"] == "null" ? " main_photo=null," : " main_photo='{$p["main_photo"]}',");
            }
            $qSet = (empty($qSet) ? "" : substr($qSet, 0, -1));
            if (empty($qSet)) {
                throw new \Exception("Запрос пустой не имеет параметров.");
            }

            // пишем в базу
            $q = "update catalogs set {$qSet} where user_id={$profileID} and catalog_id={$p["catalog_id"]} RETURNING *;";
            $db = $this->container['db'];
            $catalog = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            if (empty($catalog["user_id"])) {
                throw new \Exception("Такое объявление не существует.");
            }
            $q =
                " select catalogs.catalog_id as catalog_id, catalogs.user_id as user_id, catalogs.date_create, catalogs.description, " .
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
