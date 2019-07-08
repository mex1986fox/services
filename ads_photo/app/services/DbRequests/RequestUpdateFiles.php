<?php
namespace App\Services\DbRequests;

class RequestUpdateFiles
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function go(int $userID, int $entityID)
    {
        try {
            // читаем имена файлов в папке
            $files = scandir(MP_PRODIR . "/public/photos/$userID/$entityID/origin");
            $origin = array();
            $mini = array();
            $mainPhoto = "";
            foreach ($files as $key => $file) {
                $key = str_replace(".jpg", "", $file);
                if ($file != "." && $file != "..") {
                    if ($mainPhoto == "") {$mainPhoto = $key;}
                    $origin[$key] = "/public/photos/{$userID}/{$entityID}/origin/{$key}.jpg";
                    $mini[$key] = "/public/photos/{$userID}/{$entityID}/mini/{$key}.jpg";
                }
            }
            $jeOrigin = json_encode($origin);
            $jeMini = json_encode($mini);
            $db = $this->container['db'];
            $q =
                " insert into photos (user_id, entity_id, main, origin, mini) values ({$userID}, {$entityID}, '{$mainPhoto}', '{$jeOrigin}', '{$jeMini}')" .
                " on conflict (user_id, entity_id) do " .
                " update set origin='{$jeOrigin}', mini='{$jeMini}'; ";
            $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            return true;
        } catch (RuntimeException | \Exception $e) {
            return $e->getMessage();
        }

    }
}
