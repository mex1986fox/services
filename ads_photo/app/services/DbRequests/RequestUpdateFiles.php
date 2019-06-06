<?php
namespace App\Services\DbRequests;

class RequestUpdateFiles
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function go(int $userID, int $adID)
    {
        try {
            // читаем имена файлов в папке
            $files = scandir(MP_PRODIR . "/public/photos/$userID/$adID/origin");
            $origin = array();
            foreach ($files as $key => $file) {
                $key=str_replace(".jpg", "", $file);
                if ($file != "." && $file != "..") {
                    $origin[$key] = "/public/photos/$userID/$adID/origin/" . $file;
                }
            }
            $files = scandir(MP_PRODIR . "/public/photos/$userID/$adID/mini");
            $mini = array();
            foreach ($files as $key => $file) {
                $key=str_replace(".jpg", "", $file);
                if ($file != "." && $file != "..") {
                    $mini[$key] = "/public/photos/$userID/$adID/mini/" . $file;
                }
            }

            // определить есть ли запись в базе
            $db = $this->container['db'];
            $q = "select * from photos where user_id={$userID} and ad_id={$adID}";
            $user = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            // если нет добавить ее
            if (empty($user["user_id"])) {
                $q = "insert into photos (user_id, ad_id) values ({$userID}, {$adID})";
                $user = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
                $album = ["main" => null, "files" => ["origin" => [], "mini" => []]];
            } else {
                // вытянуть из базы объект файлов
                $album = json_decode($user["albums"], 1);
            }
            // заполнить объект альбома
            $album["files"]["origin"] = $origin;
            $album["files"]["mini"] = $mini;
            $albumString = json_encode($album);
            // записать в базу
            $q = "update photos set albums='{$albumString}' where user_id={$userID} and ad_id={$adID}";
            $user = $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            return true;
        } catch (RuntimeException | \Exception $e) {
            return $e->getMessage();
        }

    }
}
