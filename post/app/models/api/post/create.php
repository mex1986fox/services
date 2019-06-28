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
            if (empty($p["city_id"])) {
                $exceptions["city_id"] = "Не указан.";
            }
            if (empty($p["model_id"])) {
                $exceptions["model_id"] = "Не указан.";
            }

            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            $title = $p["title"];
            $description = $p["description"];
            $cityID = $p["city_id"];
            $modelID = $p["model_id"];
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
            if (!is_numeric($cityID)) {
                $exceptions["city_id"] = "Не соответствует типу integer.";
            }
            if (!is_numeric($modelID)) {
                $exceptions["model_id"] = "Не соответствует типу integer.";
            }

            //проверить токин
            //формируем токен

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $profileID = $tokenStructur->getUserID();
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
                    (user_id, title, description, city_id, model_id)
                values
                    ({$userID},'{$title}','{$description}',{$cityID},{$modelID})
                returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $post = $sth->fetch();

            if (!isset($post["post_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }

            $q =
                " select posts.user_id as user_id, posts.post_id as post_id, posts.date_create, posts.title, posts.description, posts.main_photo," .
                " cities.city_id, cities.name as city, subjects.subject_id, subjects.name as subject, " .
                " countries.country_id, countries.name as country, " .
                " models.model_id, models.name as model, brands.brand_id, brands.name as brand, " .
                " types.type_id, types.name as type,  " .
                " votes.likes as likes, votes.dislikes as dislikes " .
                " from posts " .
                " LEFT JOIN cities ON cities.city_id = posts.city_id " .
                " LEFT JOIN subjects ON subjects.subject_id = cities.subject_id " .
                " LEFT JOIN countries ON countries.country_id = cities.country_id " .
                " LEFT JOIN models ON models.model_id = posts.model_id " .
                " LEFT JOIN brands ON brands.brand_id = models.brand_id " .
                " LEFT JOIN types ON types.type_id = models.type_id " .
                " LEFT JOIN votes ON votes.user_id = posts.user_id  and votes.post_id = posts.post_id " .
                " WHERE posts.user_id={$profileID} and posts.post_id={$post['post_id']}";
            $postSelect = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            return ["status" => "ok",
                "data" => ["post" => $postSelect],
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
