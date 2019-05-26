<?php
namespace App\Models\Api\Photos;

use \App\Services\Structur\TokenStructur;

class Upload
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

            $valid = $this->container['validators'];
            $vMethods = $valid->MethodsValidator;
            // проверяем обязательные для ввода

            if (!$vMethods->isValid([
                "emptyParams" => [
                    ["access_token", $p],
                    ["post_id", $p],
                    ["files", $_FILES],
                ]])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            };

            //передаем в переменные
            $postID = $p["post_id"];
            $files = $_FILES["files"]["name"];
            $accessToken = $p["access_token"];
            // проверяем параметры
            if (!$vMethods->isValid([
                "isNumeric" => [["post_id", $postID]],
                "isAccessToken" => [["access_token", $accessToken]],
                "isArray" => [["files", $files]],
            ])) {
                $exceptions = $vMethods->getExceptions();
                throw new \Exception("Ошибки в параметрах.");
            }

            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);

            $vLoadImg = $valid->ImgValidator;
            //вынимаем из токена id юзера
            $userID = $tokenStructur->getUserID();
            $postID = $p["post_id"];
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
                    $path = MP_PRODIR . "/public/photos/$userID/$postID/origin";
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    imagejpeg($file, $path . "/" . $name);
                    // сохранить миниатюру файл на сервер
                    $filemini = $cImg->isTransform($path . "/" . $name, 320);
                    $pathmini = MP_PRODIR . "/public/photos/$userID/$postID/mini";
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
            $dbrUFStatus = $dbrUF->go($userID, $postID);
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
