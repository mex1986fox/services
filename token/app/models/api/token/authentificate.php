<?php
namespace App\Models\Api\Token;

use \Zend\Validator\Exception\RuntimeException as RuntimeException;

class Authentificate
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
        // проводит авторизацию логина и пароля
        // в случае успеха генерирует Access и Refresh токены
        // которые возвращает клиентской программе
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
                $exceptions["login"] = "Не указан";
            }
            if (empty($password)) {
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
            // // пишем в базу
            // $db = $this->container['db'];
            // $q = "select * from identifications where login = '{$login}';";
            // $sth = $db->query($q, \PDO::FETCH_ASSOC);
            // $user = $sth->fetch();

            // if (!isset($user["id"])) {
            //     $exceptions["login"] = "Такой логин не зарегистрирован";
            //     throw new \Exception("Ошибки в параметрах");
            // }
            // if ($user['password'] !== md5($password)) {
            //     $exceptions["password"] = "Не верный пароль";
            //     throw new \Exception("Ошибки в параметрах");
            // }

            // создаем токены доступа
            // alg - алгоритм шифрования
            // exp – дата истечения срока действия
            // iat – время создания
            //

            $AccessTokenHeader = '{"alg":"HS256","typ":"JWT"}';
            $AccessTokenIat = time(); //время создания токена
            $AccessTokenExp = (time() + (30 * 60)); //время смерти токена после которого он не актуален
            $AccessTokenPayload = '{"userID": 15,"iat": ' . $AccessTokenIat . ', "exp":' . $AccessTokenExp . '}';
            $AccessTokenHPEncode = base64_encode($AccessTokenHeader) . "." . base64_encode($AccessTokenPayload);
            $AccessTokenSecretKey = md5(uniqid(rand(), 1));
            $AccessTokenSignature = hash_hmac('sha256', $AccessTokenHPEncode, $AccessTokenSecretKey);
            $AccessToken = $AccessTokenHPEncode . "." . $AccessTokenSignature;

            $RefreshTokenHeader = '{"alg":"HS256","typ":"JWT"}';
            $RefreshTokenIat = time(); //время создания токена
            $RefreshTokenExp = (time() + (30 * 24 * 60 * 60)); //время смерти токена после которого он не актуален
            $RefreshTokenPayload = '{"userID": 15,"iat": ' . $RefreshTokenIat . ', "exp":' . $RefreshTokenExp . '}';
            $RefreshTokenHPEncode = base64_encode($RefreshTokenHeader) . "." . base64_encode($RefreshTokenPayload);
            $RefreshTokenSecretKey = md5(uniqid(rand(), 1));
            $RefreshTokenSignature = hash_hmac('sha256', $RefreshTokenHPEncode, $RefreshTokenSecretKey);
            $RefreshToken = $RefreshTokenHPEncode . "." . $RefreshTokenSignature;

            //отправить всем сервисам запросы на удаление токенов

            return ["status" => "ok",
                "data" => [
                    "AccessToken" => $AccessToken,
                    // "AccessTokenExp" => $AccessTokenExp,
                    // "AccessTokenHeader" => $AccessTokenHeader,
                    // "AccessTokenPayload" => $AccessTokenPayload,
                    // "AccessTokenHPEncode" => $AccessTokenHPEncode,
                    // "AccessTokenSecretKey" => $AccessTokenSecretKey,
                    // "AccessTokenSignature" => $AccessTokenSignature,

                    "RefreshToken" => $RefreshToken,
                    // "RefreshTokenExp" => $RefreshTokenExp,
                    // "RefreshTokenPayload" => $RefreshTokenPayload,
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
