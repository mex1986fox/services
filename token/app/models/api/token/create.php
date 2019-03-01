<?php
namespace App\Models\Api\Token;

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
        // создает новое поле в базе token
        // записывает туда id пользователя логин и пароль
        // данная операция доступна только доверенным сервисам
        // от внешнего мира должна быть скрыта
        try {
            // передаем параметры в переменные
            $p = $this->request->getQueryParams();
            $exceptions = [];
            $userID = $p["user_id"];
            $login = $p["login"];
            $password = $p["password"];

            // проверяем параметры
            $valid = $this->container['validators'];
            $vStLen = $valid->StringLength;
            if (empty($userID)) {
                $exceptions["user_id"] = "Не указан.";
            }
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
            if (!$vStLen->isValid($userID)) {
                $exceptions["user_id"] = "Не соответсвует диапозону длины.";
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
            // проверяем наличие в базе
            
            // пишем в базу
            $db = $this->container['db'];
            $q ="insert into tokens
                    (user_id, login, password )
                values
                    ({$userID},'{$login}',md5('{$password}')) 
                returning user_id;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }
            // отправить всем сервисам запросы на удаление токенов
            return ["status" => "ok",
                "data" => null,
            ];
        } catch (RuntimeException | \Exception $e) {

            $exceptions["massege"] = $e->getMessage();
            if(strpos($exceptions["massege"], 'Ключ "(user_id)=(') !== false){
                $exceptions["user_id"] = "Уже существует.";
            }
            if(strpos($exceptions["massege"], 'Ключ "(login)=(') !== false){
                $exceptions["login"] = "Не уникален.";
            }
            return [
                "status" => "except",
                "data" => $exceptions,
            ];
        }

    }
}
