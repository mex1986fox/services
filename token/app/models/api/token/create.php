<?php
namespace App\Models\Api\Token;

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
        // создает новые токены доступа
        // используя логин и пароль
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
            $AccessTokenIat=time();//время создания токена
            $AccessTokenExp=time() + (30);//время смерти токена после которого он не актуален
            $AccessTokenPayload = '{"userID": 15,"iat": '.$AccessTokenIat.', "exp":'.$AccessTokenExp.'}';
            $AccessTokenHPEncode = base64_encode($AccessTokenHeader) . "." . base64_encode($AccessTokenPayload);
            $AccessTokenSecretKey = $this->container["keys"]["AccessToken"];
            $AccessTokenSignature = hash_hmac('sha256', $AccessTokenHPEncode, $AccessTokenSecretKey);
            $AccessToken = $AccessTokenHPEncode . "." . $AccessTokenSignature;

            //отправить всем сервисам запросы на удаление токенов

            return ["status" => "ok",
                "data" => [
                    "AccessTokenHeader" => $AccessTokenHeader,
                    "AccessTokenPayload" => $AccessTokenPayload,
                    "AccessTokenHPEncode" => $AccessTokenHPEncode,
                    "AccessTokenSecretKey" => $AccessTokenSecretKey,
                    "AccessTokenSignature" => $AccessTokenSignature,
                    "AccessToken" => $AccessToken,
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
