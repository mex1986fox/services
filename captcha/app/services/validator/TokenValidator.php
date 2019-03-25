<?php
namespace App\Services\Validator;

use \App\Services\Structur\TokenStructur;
use \Zend\Validator\AbstractValidator;

class TokenValidator extends AbstractValidator
{
    protected $token;
    protected $tokenKey;
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function isValid($token)
    {
        if (!$token instanceof TokenStructur) {
            throw new \Exception("Не соответствует типу TokenStructure", 1);
        }
        $this->token = $token;
        // проверяем время жизни
        if (time() > $token->getLifeTime()) {
            throw new \Exception("Время жизни access_token истекло", 1);
            return false;
        }
        // ищем токен в базе проверенных токенов
        $db = $this->container['db'];
        $q = "select * from tokens where user_id=" . $token->getUserID();
        $qtoken = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
        // если нет записи проверяем через сервер токенов
        if (!isset($qtoken["user_id"])) {
            if (!$this->authorizateToken()) {
                throw new \Exception("Acces_token не прошел авторизацию", 1);
                return false;
            } else {
                // если все ок записываем в базу проверенных токенов
                $this->saveTokenDB();
                return true;
            }
        }

        $qAccwssToken = json_decode($qtoken["access_tokens"], 1);
        // если есть запись но токен пустой проверяем через сервер токенов
        if (empty($qAccwssToken)) {
            if (!$this->authorizateToken()) {
                throw new \Exception("Acces_token не прошел авторизацию", 1);
                return false;
            } else {
                // если все ок записываем в базу проверенных токенов
                $this->updateTokenDB();
                return true;
            }
        }

        // если есть запись и токен не пустой сверяем токены
        if ($qAccwssToken[0] == $this->token->getToken()) {
            return true;
        } else {
            // если токены не совпадают
            // пробуем сверить c token сервисом
            if (!$this->authorizateToken()) {
                throw new \Exception("Acces_token не прошел авторизацию", 1);
                return false;
            } else {
                // если все ок записываем в базу проверенных токенов
                $this->updateTokenDB();
                return true;
            }
        }
        return false;
    }

    protected function authorizateToken()
    {
        // посылаем запрос к микросервису токенов
        // для проверки токена для юзера
        $apiReqwests = $this->container['api-requests'];
        $rToken = $apiReqwests->RequestToToken;
        return $rToken->go("/api/token/authorizate", ["access_token" => $this->token->getToken()]);
    }
    protected function saveTokenDB()
    {
        // пишем в базу
        $db = $this->container['db'];
        $q = "insert into tokens
                (user_id, access_tokens)
            values
                ({$this->token->getUserID()},'[\"{$this->token->getToken()}\"]'::jsonb)
            returning user_id;";
        $sth = $db->query($q, \PDO::FETCH_ASSOC);
        $user = $sth->fetch();

        if (!isset($user["user_id"])) {
            throw new \Exception("Запись в базу не удалась.");
        }
        return false;
    }
    protected function updateTokenDB()
    {
        // пишем в базу
        $db = $this->container['db'];
        $q = "update tokens
                set access_tokens = '[\"{$this->token->getToken()}\"]'::jsonb
                where user_id = '{$this->token->getUserID()}' returning *;";
        $sth = $db->query($q, \PDO::FETCH_ASSOC);
        $user = $sth->fetch();

        if (!isset($user["user_id"])) {
            throw new \Exception("Запись в базу не удалась.");
        }
        return false;
    }
}
//update tokens set access_tokens = '[]'::jsonb where user_id = 1 returning *;
