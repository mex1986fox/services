<?php
namespace App\Models\Api\Post;

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
        // создает новых юзеров
        try {
            $p = $this->request->getQueryParams();
            $exceptions = [];
            // проверяем параметры
            $valid = $this->container['validators'];
            $vStLen = $valid->StringLength;
            if (empty($p["title"])) {
                $exceptions["title"] = "Не указан.";
            }
            if (empty($p["description"])) {
                $exceptions["description"] = "Не указан.";
            }
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            $title = $p["title"];
            $description = $p["description"];
            $accessToken = $p["access_token"];

            $vStLen->setMin(1);
            $vStLen->setMax(70);
            if (!$vStLen->isValid($title)) {
                $exceptions["title"] = "Не соответсвует диапозону длины.";
            }
            $vStLen->setMin(1);
            $vStLen->setMax(1600);
            if (!$vStLen->isValid($description)) {
                $exceptions["description"] = "Не соответсвует диапозону длины.";
            }
            //проверить токин
            //формируем токен

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
            $userID = $tokenStructur->getUserID();

            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            // пишем в базу
            $db = $this->container['db'];
            $q = "insert into posts
                    (user_id, title, description )
                values
                    ({$userID},'{$title}','{$description}')
                returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $post = $sth->fetch();

            if (!isset($post["post_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }
            return ["status" => "ok",
                "data" => $post,
            ];
        } catch (RuntimeException | \Exception $e) {

            $exceptions["massege"] = $e->getMessage();
            if (strpos($exceptions["massege"], 'Ключ "(user_id, title)=(') !== false) {
                $exceptions["title"] = "Уже существует.";
                $exceptions["massege"] = "Ошибки в параметрах.";
            }
            if (strpos($exceptions["massege"], 'Ключ "(user_id, description)=(') !== false) {
                $exceptions["description"] = "Не уникален.";
                $exceptions["massege"] = "Ошибки в параметрах.";
            }
            return [
                "status" => "except",
                "data" => $exceptions,
            ];
        }
    }
}
