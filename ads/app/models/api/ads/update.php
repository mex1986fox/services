<?php
namespace App\Models\Api\Post;

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
        // обновляет юзеров
        try {

            // передаем параметры в переменные
            $p = $this->request->getQueryParams();

            $exceptions = [];
            if (empty($p["post_id"])) {
                $exceptions["post_id"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (!is_numeric($p["post_id"])) {
                $exceptions["post_id"] = "Не соответствует типу integer.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $postID = $p["post_id"];
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }

            $accessToken = $p["access_token"];
            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);

            // проверяем параметры
            $valid = $this->container['validators'];
            $tokenSKey = $this->container['services']['token']['key_access_token'];
            $vToken = $valid->TokenValidator;
            $vToken->setKey($tokenSKey);
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["access_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }

            $vStLen = $valid->StringLength;
            $vStLen->setMin(1);
            $vStLen->setMax(70);
            if (isset($p["title"])) {
                if ($p["title"] == "") {
                    $exceptions["title"] = "Пустое значение.";
                }
                if (!$vStLen->isValid($p["title"])) {
                    $exceptions["title"] = "Не соответсвует диапозону длины.";
                }
            }
            $vStLen->setMin(1);
            $vStLen->setMax(1600);
            if (isset($p["description"])) {
                if ($p["description"] == "") {
                    $exceptions["description"] = "Пустое значение.";
                }
                if (!$vStLen->isValid($p["description"])) {
                    $exceptions["description"] = "Не соответсвует диапозону длины.";
                }
            }
            if (isset($p["city_id"])) {
                if ($p["city_id"] == "") {
                    $exceptions["city_id"] = "Пустое значение.";
                }
                if (!is_numeric($p["city_id"])) {
                    $exceptions["city_id"] = "Не соответствует типу integer.";
                }
            }
            if (isset($p["model_id"])) {
                if ($p["model_id"] == "") {
                    $exceptions["model_id"] = "Пустое значение.";
                }
                if (!is_numeric($p["model_id"])) {
                    $exceptions["model_id"] = "Не соответствует типу integer.";
                }
            }
            if (isset($p["main_photo"])) {
                if ($p["main_photo"] == "") {
                    $exceptions["main_photo"] = "Пустое значение.";
                }
                $vUri = $valid->Uri;
                if (!$vUri->isValid($p["main_photo"])) {
                    $exceptions["main_photo"] = "Не соответствует типу uri.";
                }
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            // пишем в базу
            // формируем запрос
            $qSet = "";
            $qSet = $qSet . (empty($p["title"]) ? "" : " title='{$p["title"]}',");
            $qSet = $qSet . (empty($p["description"]) ? "" : " description='{$p["description"]}',");
            $qSet = $qSet . (empty($p["city_id"]) ? "" : " city_id='{$p["city_id"]}',");
            $qSet = $qSet . (empty($p["model_id"]) ? "" : " model_id='{$p["model_id"]}',");
            if (!empty($p["main_photo"])) {
                $qSet = $qSet . ($p["main_photo"] == "null" ? " main_photo=null," : " main_photo='{$p["main_photo"]}',");
            }
            $qSet = (empty($qSet) ? "" : substr($qSet, 0, -1));
            if (empty($qSet)) {
                throw new \Exception("Запрос пустой не имеет параметров.");
            }
            $q = "update posts set {$qSet} where user_id={$tokenStructur->getUserID()} and post_id={$postID} RETURNING *;";
            $db = $this->container['db'];
            $post = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            if (empty($post["user_id"])) {
                throw new \Exception("Такой пост не существует.");
            }
            return ["status" => "ok",
                "data" => ["post" => $post],
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