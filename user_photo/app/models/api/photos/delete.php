<?php
namespace App\Models\Api\Photos;

use \App\Services\Structur\TokenStructur;

class Delete
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
        // обновляет юзеров
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            if (empty($p["access_token"])) {
                $exceptions["access_token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (empty($p["name_files"])) {
                $exceptions["name_files"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (!is_array($p["name_files"])) {
                $exceptions["name_files"] = "Должен быть массивом.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $files = $p["name_files"];
            $accessToken = $p["access_token"];
            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);

            // проверяем параметры
            $valid = $this->container['validators'];
            $tokenSKey=$this->container['services']['token']['key_access_token'];
            $vToken = $valid->TokenValidator;
            $vToken->setKey($tokenSKey);
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["access_token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }
            $vLoadImg = $valid->ImgValidator;
            //вынимаем из токена id юзера
            $userID = $tokenStructur->getUserID();
            $vStLen = $valid->StringLength;
            $vStLen->setMin(13);
            $vStLen->setMax(13);
            foreach ($files as $key => $file) {
                // проверка имени файла
                if (!$vStLen->isValid($file)) {
                    // если не прошел проверку
                    $exceptions[$file] = "Не валидное имя файла";
                } else {
                    // удалить файл с сервера
                    $name = $file . ".jpg";
                    $path = MP_PRODIR . "/public/photos/$userID/origin";
                    // echo $path . "/" . $name;
                    if (file_exists($path . "/" . $name)) {
                        unlink($path . "/" . $name);
                    }
                    // удалить миниатюру с сервера
                    $name = $file . ".jpg";
                    $path = MP_PRODIR . "/public/photos/$userID/mini";
                    if (file_exists($path . "/" . $name)) {
                        unlink($path . "/" . $name);
                    }
                }
            }
            if(!empty($exceptions)){
                throw new \Exception("Ошибки в параметрах.");
            }
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
            return [
                "status" => "except",
                "data" => $exceptions,
            ];
        }

    }

}
