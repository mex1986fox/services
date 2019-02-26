<?php
namespace App\Models\Api\Identification;

use \Zend\Validator\Exception\RuntimeException as RuntimeException;

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
        // создать идентификацию может только юзер
        // используя логин и пароль
        // короче это аутентификация
        try {
            // if (isset($_SESSION["user_id"])) {
            //     throw new \Exception("Пользователь уже авторизован");
            // }
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            $login = $p["login"];
            $password = $p["password"];

            // проверяем параметры
            $valid = $this->container['validators'];
            $vStLen = $valid->StringLength;
            if(empty($login)){
                $exceptions["login"] = "Не указан";
            }
            if(empty($password)){
                $exceptions["password"] = "Не указан";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах");
            }
            $vStLen->setMin(1);
            $vStLen->setMax(64);
            if (!$vStLen->isValid($login)) {
                $exceptions["login"] = "Не соответсвует диапозону длины";
            }
            $vStLen->setMin(4);
            $vStLen->setMax(32);
            if (!$vStLen->isValid($password)) {
                $exceptions["password"] = "Не соответсвует диапозону длины";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах");
            }
            // пишем в базу
            $db = $this->container['db'];
            $q = "select * from users where login = '{$login}';";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["id"])) {
                $exceptions["login"] = "Такой логин не зарегистрирован";
                throw new \Exception("Ошибки в параметрах");
            }
            if ($user['password'] !== md5($password)) {
                $exceptions["password"] = "Не верный пароль";
                throw new \Exception("Ошибки в параметрах");
            }
            if ($user['password'] == md5($password)) {
                $_SESSION["user_id"] = $user["id"];
                setcookie("user_id", $user["id"], time() + 60 * 60 * 12, "/");
            }
            return ["status" => "ok",
                "data" => null];
        } catch (RuntimeException | \Exception $e) {
            $exceptions['massege'] = $e->getMessage();
            return [
                "status" => "excepts",
                "data" => $exceptions
            ];
        }
    }
}
