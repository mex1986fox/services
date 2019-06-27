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
            $limit = 5;
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
                    ["user_id", $p], ["users_id", $p],
                    ["post_id", $p], ["posts_id", $p],
                    ["sort_id", $p], ["page", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],
                    ["models_id", $p], ["brands_id", $p], ["types_id", $p],
                    ["title", $p],
                ],
                "isInt" => [
                    ["user_id", $p],
                    ["post_id", $p],
                    ["ad_id", $p],
                    ["sort_id", $p], ["page", $p],
                ],
                "isArray" => [
                    ["users_id", $p],
                    ["posts_id", $p],
                    ["countries_id", $p], ["subjects_id", $p], ["cities_id", $p],
                    ["models_id", $p], ["brands_id", $p], ["types_id", $p],
                ],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            //формируем условия сортировки
            $qSort .= $sortID == 0 ? "posts.date_create DESC,  " : "";
            $qSort .= $sortID == 1 ? "posts.date_create DESC,  " : "";
            $qSort .= $sortID == 2 ? "posts.date_create ASC,  " : "";
            $qSort .= $sortID == 3 ? "cities.name ASC,  " : "";
            $qSort .= $sortID == 4 ? "cities.name desc,  " : "";
            $qSort .= $sortID == 5 ? "models.name ASC,  " : "";
            $qSort .= $sortID == 6 ? "models.name desc,  " : "";

            // строим запрос выборки
            $qWhere = $qWhere . (empty($p["user_id"]) ? "" : " posts.user_id=" . $p["user_id"] . " and ");
            $qWhere = $qWhere . (empty($p["users_id"]) ? "" : "posts.user_id in (" . implode(', ', $p["users_id"]) . ") and ");
            $qWhere = $qWhere . (empty($p["post_id"]) ? "" : " posts.post_id=" . $p["post_id"] . " and ");
            $qWhere = $qWhere . (empty($p["posts_id"]) ? "" : "posts.post_id in (" . implode(', ', $p["posts_id"]) . ") and ");

            //для местоположения
            $qWhereLocat = "";
            $qWhereLocat .= empty($p["countries_id"]) ? "" : " countries.country_id in (" . implode(', ', $p["countries_id"]) . ") or ";
            $qWhereLocat .= empty($p["subjects_id"]) ? "" : " subjects.subject_id in (" . implode(', ', $p["subjects_id"]) . ") or ";
            $qWhereLocat .= empty($p["cities_id"]) ? "" : " cities.city_id in (" . implode(', ', $p["cities_id"]) . ") or ";
            if (!empty($qWhereLocat)) {
                $qWhere .= " (" . rtrim($qWhereLocat, ' or ') . ") and ";
            }
            //для транспорта
            $qWhereTrans = "";
            $qWhereTrans .= empty($p["models_id"]) ? "" : " models.model_id in (" . implode(', ', $p["models_id"]) . ") or ";
            $qWhereTrans .= empty($p["brands_id"]) ? "" : " brands.brand_id in (" . implode(', ', $p["brands_id"]) . ") or ";
            $qWhereTrans .= empty($p["types_id"]) ? "" : " types.type_id in (" . implode(', ', $p["types_id"]) . ") or ";
            if (!empty($qWhereTrans)) {
                $qWhere .= " (" . rtrim($qWhereTrans, ' or ') . ") and ";
            }

            $qWhere = $qWhere . (empty($p["title"]) ? "" : " posts.title ILIKE '%" . $p["title"] . "%' and ");

            $qWhere = $qWhere;
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' or ');
            $qWhere = empty($qWhere) ? "" : rtrim($qWhere, ' and ');
            $qWhere = (empty($qWhere) ? "" : " where ") . $qWhere;
            $qSort = empty($qSort) ? "" : rtrim($qSort, ', ');
            $qSort = (empty($qSort) ? "" : " ORDER BY ") . $qSort . ($page > 1 ? " OFFSET " . ($page * $limit - $limit) : "");

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
                $qWhere . $qSort . " LIMIT " . $limit;
            $posts = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            return ["status" => "ok",
                "data" => ["page" => $page, "posts" => $posts],
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
