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

            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем обязательные для ввода
            if (!$vMethods->isValid([
                "emptyParams" => [
                    ["access_token", $p],
                    ["user_id", $p],
                    ["post_id", $p],
                ],
                "isSetParams" => [
                    ["vote", $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            $accessToken = $p["access_token"];
            $userID = $p["user_id"];
            $postID = $p["post_id"];
            $vote = $p["vote"];

            // проверяем параметры
            if (!$vMethods->isValid([
                "isAccessToken" => [["access_token", $accessToken]],
                "isNumeric" => [
                    ["post_id", $postID],
                    ["user_id", $userID]],
                "isBool" => [["vote", $vote]],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            $profileID = $tokenStructur->getUserID();

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
