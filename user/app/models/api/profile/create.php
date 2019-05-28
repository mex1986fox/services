<?php
namespace App\Models\Api\Profile;

use \App\Services\Structur\CaptchaTokenStructur;

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
            $p = $this->request->getQueryParams();
            $exceptions = [];
            // проверяем параметры
            $valid = $this->container['validators'];
            $vStLen = $valid->StringLength;
            if (empty($p["login"])) {
                $exceptions["login"] = "Не указан.";
            }
            if (empty($p["password"])) {
                $exceptions["password"] = "Не указан.";
            }
            if (empty($p["captcha_token"])) {
                $exceptions["captcha_token"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            $login = $p["login"];
            $password = $p["password"];
            $tokenCaptcha = $p["captcha_token"];

            $vStLen->setMin(1);
            $vStLen->setMax(64);
            if (!$vStLen->isValid($login)) {
                $exceptions["login"] = "Не соответсвует диапозону длины.";
            }
            $vStLen->setMin(4);
            $vStLen->setMax(32);
            if (!$vStLen->isValid($password)) {
                $exceptions["password"] = "Не соответсвует диапозону длины.";
            }
            // шифруем пароль
            $password = password_hash($password, PASSWORD_DEFAULT);
            //проверить токин
            //формируем токен

            $tokenStructur = new CaptchaTokenStructur($this->container);
            $tokenStructur->setToken($tokenCaptcha);
            $vToken = $valid->TokenCaptchaValidator;
            $TokenKey = $this->container["services"]["token"]["key_captcha_token"];
            $vToken->setKey($TokenKey);
            if (!$vToken->isValid($tokenStructur)) {
                $exceptions["token"] = "Не действителен.";
                throw new \Exception("Ошибки в параметрах.");
            }

            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }
            // проверяем наличие в базе

            // пишем в базу
            $db = $this->container['db'];
            $q = "insert into users
                    (login, password )
                values
                    ('{$login}','{$password}')
                returning user_id;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                throw new \Exception("Запись в базу не удалась.");
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
}
