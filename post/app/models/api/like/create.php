<?php
namespace App\Models\Api\Like;

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
            if (!isset($p["vote"])) {
                $exceptions["vote"] = "Не указан.";
            }
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
            }
            if (empty($p["user_id"])) {
                $exceptions["user_id"] = "Не указан.";
            }
            if (empty($p["post_id"])) {
                $exceptions["post_id"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }
            $vote = filter_var((integer) $p["vote"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $accessToken = $p["access_token"];
            $userID = $p["user_id"];
            $postID = $p["post_id"];

            if ($vote === null) {
                $exceptions["vote"] = "Не соответсвует типу boolian.";
            }
            if (!is_numeric($userID)) {
                $exceptions["user_id"] = "Не соответствует типу integer.";
            }
            if (!is_numeric($postID)) {
                $exceptions["post_id"] = "Не соответствует типу integer.";
            }
            //проверить токин
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
            $profileID = $tokenStructur->getUserID();

            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            $db = $this->container['db'];
            // формируем запрос на проверку такого поста
            $q = "select post_id from posts where user_id={$userID} and post_id={$postID}";
            $fCheckPost = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            //если нет поста
            if (empty($fCheckPost["post_id"])) {
                throw new \Exception("Такого поста нет.");
            }
            // формируем запрос на проверку проголосовавших
            $q = "select post_id from votes where user_id={$userID} and post_id={$postID} and profiles @>'{{$profileID}}'::integer[]";
            $fCheckVote = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            //если голосовал
            if (!empty($fCheckVote["post_id"])) {
                throw new \Exception("Пользователь уже голосовал");
            }
            // формируем запрос на добавление голоса
            $qLike = ($vote == true ? 1 : 0);
            $qDisLike = ($vote == false ? 1 : 0);
            $q =
                " insert into votes (user_id, post_id, likes, dislikes, profiles) " .
                " values ({$userID}, {$postID}, {$qLike}, {$qDisLike}, '{{$profileID}}'::integer[]) " .
                " on conflict (user_id, post_id) do " .
                " update set user_id={$userID}, post_id={$postID}, " .
                " likes=votes.likes+{$qLike}, dislikes=votes.dislikes+{$qDisLike}, " .
                " profiles=votes.profiles||{$profileID}";
            $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            return ["status" => "ok",
                "data" => null,
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
