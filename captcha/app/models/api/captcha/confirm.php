<?php
namespace App\Models\Api\Captcha;

use \App\Services\Structur\CaptchaTokenStructur;

class Confirm
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
        // проверяет правильно ли введена каптча
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            if (empty($p["token"])) {
                $exceptions["token"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if (empty($p["answer"])) {
                $exceptions["answer"] = "Не указан.";
                throw new \Exception("Ошибки в параметрах.");
            }
            //формируем токен
            $answer = $p["answer"];
            $token = $p["token"];
            $tokenStructur = new CaptchaTokenStructur($this->container);
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
            //проверяем в базе
            $q = "select answer from captcha where captcha_id=" . $tokenStructur->getCaptchaID();
            $tokenDB = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            if (empty($tokenDB["answer"])) {
                $exceptions["answer"] = "Отсутствует в базе.";
                throw new \Exception("Ошибки в параметрах.");
            }
            // если не верный ответ
            // удаляем ответ из базы и статус ставим в false
            if ($tokenDB["answer"] != md5($answer)) {
                $q = "update captcha set answer='', status = false where captcha_id=" . $tokenStructur->getCaptchaID();
                $tokenDB = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
                $exceptions["answer"] = "Не верный.";
                throw new \Exception("Ошибки в параметрах.");

            }
            // если верный ответ
            // удаляем ответ из базы и статус ставим в true
            $q = "update captcha set answer='', status = true where captcha_id=" . $tokenStructur->getCaptchaID();
            $tokenDB = $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            //формируем токен для ответа со статусом пройдено
            $tokenStructurAns = new CaptchaTokenStructur($this->container);
            $tokenStructurAns->initToken($tokenStructur->getCaptchaID(), "true");

            return ["status" => "ok",
                "data" => [
                    "token"=>$tokenStructurAns->getToken()
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
