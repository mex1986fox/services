<?php
namespace App\Models\Api\Post;

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

            $sortID = empty($p["sort_id"]) ? 0 : $p["sort_id"];
            //для пагинации от какого id юзера шагать при выборке
            $stepFrom = empty($p["step_from"]) ? "" : $p["step_from"];

            $postID = empty($p["post_id"]) ? "" : $p["post_id"];
            $title = empty($p["title"]) ? "" : $p["title"];
            // $name = empty($p["name"]) ? "" : $p["name"];
            // $surname = empty($p["surname"]) ? "" : $p["surname"];
            $countriesID = empty($p["countries_id"]) ? "" : array_diff($p["countries_id"], array(''));
            $subjectsID = empty($p["subjects_id"]) ? "" : array_diff($p["subjects_id"], array(''));
            $citiesID = empty($p["cities_id"]) ? "" : array_diff($p["cities_id"], array(''));

            $modelsID = empty($p["models_id"]) ? "" : array_diff($p["models_id"], array(''));
            $brandsID = empty($p["brands_id"]) ? "" : array_diff($p["brands_id"], array(''));
            $typesID = empty($p["types_id"]) ? "" : array_diff($p["types_id"], array(''));
            // проверяем параметры
            // if (empty($sortID)) {
            //     $exceptions["sort_id"] = "Не указан.";
            //     throw new \Exception("Ошибки в параметрах.");
            // }
            // if (!is_numeric($sortID)) {
            //     $exceptions["sort_id"] = "Не соответствует типу integer.";
            //     throw new \Exception("Ошибки в параметрах.");
            // }
            // if ($sortID < 1 || $sortID > 2) {
            //     $exceptions["sort_id"] = "Не соответствует диапазону.";
            //     throw new \Exception("Ошибки в параметрах.");
            // }
            //формируем условия сортировки
            $qSort = "";
            $qWhere = "";
            $qWherePag = "";
            if (!empty($stepFrom)) {
                $db = $this->container['db'];
                $q = "select post_id, date_create, cities.name as city, models.name as model from posts"
                    . " LEFT JOIN cities ON cities.city_id = posts.city_id "
                    . " LEFT JOIN models ON models.model_id = posts.model_id "
                    . " where post_id=" . $stepFrom;
                $sfBlog = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
                if (empty($sfBlog)) {
                    $exceptions["step_from"] = "Не найден в системе.";
                    throw new \Exception("Ошибки в параметрах.");
                }
            }

            switch ($sortID) {
                case 0:
                    $qSort = $qSort . "posts.date_create DESC, posts.post_id DESC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (posts.date_create, posts.post_id)<('" . $sfBlog["date_create"] . "', " . $sfBlog["post_id"] . ") and ");
                    break;
                case 1:
                    $qSort = $qSort . "posts.date_create DESC, posts.post_id DESC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (posts.date_create, posts.post_id)<('" . $sfBlog["date_create"] . "', " . $sfBlog["post_id"] . ") and ");
                    break;
                case 2:
                    $qSort = $qSort . "posts.date_create ASC, posts.post_id ASC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (posts.date_create, posts.post_id)>('" . $sfBlog["date_create"] . "', " . $sfBlog["post_id"] . ") and ");
                    break;
                case 3:
                    $qSort = $qSort . "cities.name ASC, posts.post_id ASC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (cities.name, posts.post_id)>('" . $sfBlog["city"] . "', " . $sfBlog["post_id"] . ") and ");
                    break;
                case 4:
                    $qSort = $qSort . "cities.name desc, posts.post_id desc, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (cities.name, posts.post_id)<('" . $sfBlog["city"] . "', " . $sfBlog["post_id"] . ") and ");
                    break;
                case 5:
                    $qSort = $qSort . "models.name ASC, posts.post_id ASC, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (models.name, posts.post_id)>('" . $sfBlog["model"] . "', " . $sfBlog["post_id"] . ") and ");
                    break;
                case 6:
                    $qSort = $qSort . "models.name desc, posts.post_id desc, ";
                    $qWherePag = $qWherePag . (empty($sfBlog) ? "" : " (models.name, posts.post_id)<('" . $sfBlog["model"] . "', " . $sfBlog["post_id"] . ") and ");
                    break;
            }

            // строим запрос выборки
            $qWhere = $qWhere . (empty($postID) ? "" : " post_id=" . $postID . " and ");

            $qWhere = $qWhere . (empty($title) ? "" : " posts.title ILIKE '%" . $title . "%' and ");

            //для местоположения
            if (!empty($citiesID) || !empty($subjectsID) || !empty($countriesID)) {
                $qWhere = $qWhere . " (";
            } //  для страны
            $qWhere = $qWhere . (empty($countriesID) ? "" : " countries.country_id in (" . implode(', ', $countriesID) . ") or ");
            //  для региона
            $qWhere = $qWhere . (empty($subjectsID) ? "" : " subjects.subject_id in (" . implode(', ', $subjectsID) . ") or ");
            //  для города
            $qWhere = $qWhere . (empty($citiesID) ? "" : " cities.city_id in (" . implode(', ', $citiesID) . ") or ");
            $qWhere = $qWhere . (empty($modelsID) ? "" : " models.model_id in (" . implode(', ', $modelsID) . ") or ");
            $qWhere = $qWhere . (empty($brandsID) ? "" : " brands.brand_id in (" . implode(', ', $brandsID) . ") or ");
            $qWhere = $qWhere . (empty($typesID) ? "" : " types.type_id in (" . implode(', ', $typesID) . ") or ");
            if (!empty($citiesID) || !empty($subjectsID) || !empty($countriesID)) {
                $qWhere = rtrim($qWhere, ' or ') . ") and ";
            }

            $qWhere = $qWhere . $qWherePag;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;

            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort;
            // пишем в базу
            $db = $this->container['db'];
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
                " LEFT JOIN votes ON votes.user_id = posts.user_id  and votes.post_id = posts.post_id" .
                $qWhere . $qSort . " LIMIT 5";
            $posts = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["posts" => $posts],
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
