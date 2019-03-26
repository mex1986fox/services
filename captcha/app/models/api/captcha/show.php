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
            for ($i = 0; $i < rand(3, 4); $i++) {
                $scirand = rand(0, 2);
                if ($scirand == 0) {
                    $answer = $answer . chr(rand(49, 57));
                }
                if ($scirand == 1) {
                    $answer = $answer . chr(rand(97, 110));
                }
                if ($scirand == 2) {
                    $answer = $answer . chr(rand(112, 122));
                }
            }

            // создаем фон картинки
            $image = imagecreatetruecolor(200, 80);
            //Отключаем режим сопряжения цветов
            imagealphablending($image, false);
            //прозрачный
            // imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
            // не прозрачный
            imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
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
                $y = $y + rand(22, 28);
                $x = rand(45, 65);
                $color = imagecolorallocate($image, rand(0, 25), rand(0, 25), rand(0, 25));
                imagettftext($image, 35, $angle, $y, $x, $color, __dir__ . "/verdana.ttf", $answer[$i]);
            }
            for ($i = 0; $i < 100; $i++) {
                $x = rand(1, 200);
                $y = rand(1, 80);
                $color = imagecolorallocate($image, 255, 255, 255);
                imagefilledellipse($image, $x, $y, rand(4, 6), rand(4, 6), $color);
            }
            for ($i = 0; $i < 20; $i++) {
                $x1 = rand(1, 200);
                $y1 = rand(1, 80);
                $x2 = rand(1, 200);
                $y2 = rand(1, 80);
                $color = imagecolorallocate($image, 255, 255, 255);
                imageline($image, $x1, $y1, $x2, $y2, $color);
            }
            // // imagepng($image);
            // imagedestroy($image);
            ob_start();
            imagejpeg($image);
            $outputBuffer = ob_get_clean();
            $base64 = base64_encode($outputBuffer);
            // $base64img=base64_encode($image);
            //записываем в базу ответ правильный
            $q = "update captcha set answer='" . md5($answer) . "' where captcha_id=" . $tokenStructur->getCaptchaID();
            $tokenDB = $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            return ["status" => "ok",
                "data" => [
                    "src" => "data:image/jpeg;base64,".$base64,
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
