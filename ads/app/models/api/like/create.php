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
                    ["ad_id", $p],
                ],
                "isSetParams" => [
                    ["vote", $p],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            $accessToken = $p["access_token"];
            $userID = $p["user_id"];
            $adID = $p["ad_id"];
            $vote = $p["vote"];

            // проверяем параметры
            if (!$vMethods->isValid([
                "isAccessToken" => [["access_token", $accessToken]],
                "isNumeric" => [
                    ["ad_id", $adID],
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
            $q = "select ad_id from ads where user_id={$userID} and ad_id={$adID}";
            $fCheckPost = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            //если нет поста
            if (empty($fCheckPost["ad_id"])) {
                throw new \Exception("Такого поста нет.");
            }
            // формируем запрос на проверку проголосовавших
            $q = "select ad_id from votes where user_id={$userID} and ad_id={$adID} and profiles @>'{{$profileID}}'::integer[]";
            $fCheckVote = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            //если голосовал
            if (!empty($fCheckVote["ad_id"])) {
                throw new \Exception("Пользователь уже голосовал");
            }
            // формируем запрос на добавление голоса
            $qLike = ($vote == true ? 1 : 0);
            $qDisLike = ($vote == false ? 1 : 0);
            $q =
                " insert into votes (user_id, ad_id, likes, dislikes, profiles) " .
                " values ({$userID}, {$adID}, {$qLike}, {$qDisLike}, '{{$profileID}}'::integer[]) " .
                " on conflict (user_id, ad_id) do " .
                " update set user_id={$userID}, ad_id={$adID}, " .
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
