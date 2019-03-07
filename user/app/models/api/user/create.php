<?php
namespace App\Models\Api\User;

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
            $login = $p["login"];
            $password = $p["password"];

            // проверяем параметры
            $valid = $this->container['validators'];
            $vStLen = $valid->StringLength;
            if (empty($login)) {
                $exceptions["login"] = "Не указан.";
            }
            if (empty($password)) {
                $exceptions["password"] = "Не указан.";
            }
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }
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
            if (!empty($exceptions)) {
                throw new \Exception("Ошибки в параметрах.");
            }

            // пишем в базу
            $db = $this->container['db'];
            $q = "insert into users
                    (login)
                values
                    ('{$login}')
                returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }
            // посылаем запрос к микросервису токенов
            // для создания токена для юзера
            $apiReqwests = $this->container['api-requests'];
            $rCreateToken = $apiReqwests->RequestCreateToken;
            $statusCreateToken = $rCreateToken->go(["user_id" => $user["id"], "login" => $login, "password" => $password]);
            // если не удалось создать токен
            if ($statusCreateToken == false) {

                $q = "delete from users * where id={$user["id"]}";
                $sth = $db->query($q, \PDO::FETCH_ASSOC);
                $user = $sth->fetch();
                throw new \Exception("Токен не создан.");
            }
            // отправить всем сервисам запросы на удаление токенов
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