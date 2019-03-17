<?php
namespace App\Models\Api\Photos;

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
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (empty($_FILES["files"])) {
                $exceptions["files"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (!is_array($_FILES["files"]["name"])) {
                $exceptions["files"] = "Должен быть массивом.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $accessToken = $p["access_token"];
            $tokenStructur = new TokenStructur();
            $tokenStructur->setToken($accessToken);

            // проверяем параметры
            $valid = $this->container['validators'];
            $vToken = $valid->TokenValidator;
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["access_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $vLoadImg = $valid->ImgValidator;
            //вынимаем из токена id юзера
            $userID = $tokenStructur->getUserID();

            // добавляем конвертер
            $converters = $this->container['converters'];
            $cImg = $converters->ImgConverter;

            // реорганизовываем $_FILES в более удобную структуру
            $files = $this->reArrayFiles($_FILES["files"]);
            // var_dump($files);
            // перебираем в цикле пришедшие файлы
            foreach ($files as $key => $file) {
                // проверка файла
                $excLoadImg = $vLoadImg->isValid($file);
                if ($excLoadImg["error"] == true) {
                    // если не прошел проверку
                    $exceptions[$file["name"]] = $excLoadImg["massege"];
                } else {

                    // сделать ресайз картинки
                    // для того что бы убить вредоносный скрипт
                    $file = $cImg->isTransform($file['tmp_name'], 1024);
                    // сохранить файл на сервер
                    $name = uniqid() . ".jpg";
                    $path = MP_PRODIR . "/public/photos/$userID/origin";
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    imagejpeg($file, $path . "/" . $name);
                    // сохранить миниатюру файл на сервер
                    $filemini = $cImg->isTransform($path . "/" . $name, 90);
                    $pathmini = MP_PRODIR . "/public/photos/$userID/mini";
                    if (!file_exists($pathmini)) {
                        mkdir($pathmini, 0777, true);
                    }
                    imagejpeg($filemini, $pathmini . "/" . $name);

                }

                // перезаписать фалы в базу
                $this->addFilesToDb($userID);
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }
            return ["status" => "ok",
                "data" => null,
            ];
        } catch (RuntimeException | \Exception $e) {

            $exceptions["massege"] = $e->getMessage();
            if (strpos($exceptions["massege"], 'Ключ "(id)=(') !== false) {
                $exceptions["user_id"] = "Уже существует.";
            }
            if (strpos($exceptions["massege"], 'Ключ "(login)=(') !== false) {
                $exceptions["login"] = "Не уникален.";
            }
            return [
                "status" => "except",
                "data" => $exceptions,
            ];
        }
    }
    public function addFilesToDb($userID)
    {
        // добавляем базу
        // читаем имена файлов в папке
        $files = scandir(MP_PRODIR . "/public/photos/$userID/origin");
        $origin = array();
        foreach ($files as $key => $file) {
            if ($file != "." && $file != "..") {
                $origin[] = "/public/photos/$userID/origin/" . $file;
            }
        }
        $files = scandir(MP_PRODIR . "/public/photos/$userID/mini");
        $mini = array();
        foreach ($files as $key => $file) {
            if ($file != "." && $file != "..") {
                $mini[] = "/public/photos/$userID/mini/" . $file;
            }
        }

        // определить есть ли запись в базе
        $db = $this->container['db'];
        $q = "select * from photos where user_id={$userID}";
        $user = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
        // если нет добавить ее
        if (empty($user["user_id"])) {
            $q = "insert into photos (user_id) values ({$userID})";
            $user = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            $album = ["avatar" => null, "files" => ["origin" => [], "mini" => []]];
        } else {
            // вытянуть из базы объект файлов
            $album = json_decode($user["albums"], 1);
        }
        // заполнить объект альбома
        $album["files"]["origin"] = $origin;
        $album["files"]["mini"] = $mini;
        $albumString = json_encode($album);
        // записать в базу
        $q = "update photos set albums='{$albumString}' where user_id={$userID}";
        $user = $db->query($q, \PDO::FETCH_ASSOC)->fetch();

    }
    public function reArrayFiles(&$file_post)
    {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }
}
