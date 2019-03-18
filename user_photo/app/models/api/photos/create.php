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

            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            // обновим список файлов в базе
            $dbreqwests = $this->container['db-requests'];
            $dbrUF = $dbreqwests->RequestUpdateFiles;
            $dbrUFStatus = $dbrUF->go($userID);
            if ($dbrUFStatus != true) {
                throw new \Exception($dbrUFStatus);
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
