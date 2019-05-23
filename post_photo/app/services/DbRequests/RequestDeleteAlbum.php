<?php
namespace App\Services\DbRequests;

class RequestDeleteAlbum
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function go(int $userID, int $postID)
    {
        try {
            // удалить папку с содержимым

            $dir = MP_PRODIR . "/public/photos/$userID/$postID";
            $this->unlinkRecursive($dir, true);

            // удалить запись в базе
            $db = $this->container['db'];
            $q = "delete from photos where user_id={$userID} and post_id={$postID}";
            $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            return true;
        } catch (RuntimeException | \Exception $e) {
            return $e->getMessage();
        }

    }
    /**
     * Recursively delete a directory
     *
     * @param string $dir Directory name
     * @param boolean $deleteRootToo Delete specified top-level directory as well
     */
    protected function unlinkRecursive($dir, $deleteRootToo)
    {
        if (!$dh = @opendir($dir)) {
            return;
        }
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }

            if (!@unlink($dir . '/' . $obj)) {
                $this->unlinkRecursive($dir . '/' . $obj, true);
            }
        }

        closedir($dh);

        if ($deleteRootToo) {
            @rmdir($dir);
        }

        return;
    }
}
