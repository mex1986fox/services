<?php
namespace App\Models\Api\Captcha;

use \App\Services\Structur\CaptchaTokenStructur;

class Show
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
        // показывает юзеров в системе
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            if (empty($p["token"])) {
                $exceptions["token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }

            //формируем токен
            $token = $p["token"];
            $tokenStructur = new CaptchaTokenStructur();
            $tokenStructur->setToken($token);

            //ищем ключ от токена
            $db = $this->container['db'];
            $q = "select * from captcha where captcha_id = '{$tokenStructur->getCaptchaID()}';";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $captcha = $sth->fetch();
            if (!isset($captcha["captcha_id"])) {
                throw new \Exception("Такая каптча не зарегистрирована.");
            }
            $TokenKey = current(json_decode($captcha["token"], 1));
            if (empty($TokenKey)) {
                throw new \Exception("Токен отсутствует у капчи.");
            }
            //проверить токин
            $valid = $this->container['validators'];
            $vToken = $valid->TokenCaptchaValidator;
            $vToken->setKey($TokenKey);
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (!$vToken->isValidLifeTime()) {
                $exceptions["token"] = "Истекло время жизни токена.";
                throw new \Exception("Ошибки в параметрах.");
            }

            $answer = "";
            // создаем  загадку
            for ($i = 0; $i < rand(5, 7); $i++) {
                if (rand(0, 1) == 1) {
                    $answer = $answer . chr(rand(49, 57));
                } else {
                    $answer = $answer . chr(rand(97, 122));
                }
            }

            // создаем фон картинки
            $image = imagecreatetruecolor(200, 80);
            //Отключаем режим сопряжения цветов
            imagealphablending($image, false);
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
            //Включаем сохранение альфа канала
            imagesavealpha($image, true);
            $y = 0;
            for ($i = 0; $i < strlen($answer); $i++) {
                //наклон формируем
                if (rand(0, 1) == 1) {
                    $angle = rand(0, 25);
                } else {
                    $angle = rand(335, 360);
                }
                $y = $y + rand(18, 22);
                $x = rand(45, 65);
                $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
                imagettftext($image, 35, $angle, $y, $x, $color, __dir__ . "/verdana.ttf", $answer[$i]);
            }

            //записываем в базу ответ правильный
            $q = "update captcha set answer='".md5($answer)."' where captcha_id=" . $tokenStructur->getCaptchaID();
            $tokenDB = $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            return ["status" => "ok",
                "data" => [
                    "captcha" => $image,
                ],
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
