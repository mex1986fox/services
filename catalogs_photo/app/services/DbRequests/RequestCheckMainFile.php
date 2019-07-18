<?php
namespace App\Services\DbRequests;

class RequestCheckMainFile
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function go(int $userID, int $entityID, string $fileName)
    {
        try {
            // выбираем список файлов и проверяем есть ли такой
            $db = $this->container['db'];
            $q = "select origin, origin->'{$fileName}' as file from photos where user_id={$userID} and entity_id={$entityID};";
            $photos = $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            // если нет, выкидываем ошибку
            if (empty($photos)) {
                throw new \Exception("Нет такой записи.");
            }
            if (empty($photos["origin"])) {
                throw new \Exception("Список файлов пуст.");
            }
            if (empty($photos["file"])) {
                throw new \Exception("Такой файл отсутствует.");
            }
            // если есть, отмечаем главным
            $db = $this->container['db'];
            $q = "update photos set main='{$fileName}' where user_id={$userID} and entity_id={$entityID};";
            $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            return true;
        } catch (RuntimeException | \Exception $e) {
            return $e->getMessage();
        }

    }
}
