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
        // проводит проверку логина и пароля
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

            // ищем базе
            $db = $this->container['db'];
            $q = "select * from tokens where login = '{$login}';";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                $exceptions["login"] = "Такой логин не зарегистрирован.";
                throw new \Exception("Ошибки в параметрах.");
            }
            if ($user['password'] !== md5($password)) {
                $exceptions["password"] = "Не верный пароль.";
                throw new \Exception("Ошибки в параметрах.");
            }

            // проверить есть ли актуальный refresh токен
            // если есть проверить время жизни refresh токена
            // если срок актуален отдать старый
            if ($user["refresh_tokens"]) {
                $rt_SecretKey = current(json_decode($user["refresh_tokens"], true));
                $rt_Signature = key(json_decode($user["refresh_tokens"], true));
                $rt_HPEncode=openssl_decrypt($rt_Signature, 'AES256', hex2bin($rt_SecretKey), 0, hex2bin($rt_SecretKey));
                $rt_exp=explode(".",$rt_HPEncode);
                
               echo $AccessTokenHeader=base64_decode($rt_exp[0]);
               echo $AccessTokenPayload=base64_decode($rt_exp[1]);
            }

            // проверить есть ли актуальный access токен
            // если есть проверить время жизни access токена
            // если срок актуален отдать старый

            // создаем токены доступа
            // alg - алгоритм шифрования
            // exp – дата истечения срока действия
            // iat – время создания
            $AccessTokenHeader = '{"alg":"HS256","typ":"JWT"}';
            $AccessTokenIat = time(); //время создания токена
            $AccessTokenExp = (time() + (30 * 60)); //время смерти токена после которого он не актуален
            $AccessTokenPayload = '{"userID": 15,"iat": ' . $AccessTokenIat . ', "exp":' . $AccessTokenExp . '}';
            $AccessTokenHPEncode = base64_encode($AccessTokenHeader) . "." . base64_encode($AccessTokenPayload);
            // $AccessTokenSecretKey = uniqid(rand(), 0);
            $ivlen = openssl_cipher_iv_length('AES256');
            $AccessTokenSecretKey = openssl_random_pseudo_bytes($ivlen);
            
            $AccessTokenSignature = openssl_encrypt($AccessTokenHPEncode, 'AES256', $AccessTokenSecretKey, 0, $AccessTokenSecretKey);
            $AccessToken = $AccessTokenHPEncode . "." . $AccessTokenSignature;

            $RefreshTokenHeader = '{"alg":"HS256","typ":"JWT"}';
            $RefreshTokenIat = time(); //время создания токена
            $RefreshTokenExp = (time() + (30 * 24 * 60 * 60)); //время смерти токена после которого он не актуален
            $RefreshTokenPayload = '{"userID": 15,"iat": ' . $RefreshTokenIat . ', "exp":' . $RefreshTokenExp . '}';
            $RefreshTokenHPEncode = base64_encode($RefreshTokenHeader) . "." . base64_encode($RefreshTokenPayload);
            //$RefreshTokenSecretKey = uniqid(rand(), 0);
            $ivlen = openssl_cipher_iv_length('AES256');
            $RefreshTokenSecretKey = openssl_random_pseudo_bytes($ivlen);
            $RefreshTokenSignature = openssl_encrypt($RefreshTokenHPEncode, 'AES256', $RefreshTokenSecretKey, 0, $RefreshTokenSecretKey);
            $RefreshToken = $RefreshTokenHPEncode . "." . $RefreshTokenSignature;

            // записываем в базу токены
            // пишем в базу
            $AccessTokenSecretKeyB=bin2hex($AccessTokenSecretKey);
            $RefreshTokenSecretKeyB=bin2hex($RefreshTokenSecretKey);
            $q = "update tokens
                set access_tokens = '{\"{$AccessTokenSignature}\":\"{$AccessTokenSecretKeyB}\"}'::jsonb,
                    refresh_tokens = '{\"{$RefreshTokenSignature}\":\"{$RefreshTokenSecretKeyB}\"}'::jsonb
                where login = '{$login}' returning *;";
            $sth = $db->query($q, \PDO::FETCH_ASSOC);
            $user = $sth->fetch();

            if (!isset($user["user_id"])) {
                throw new \Exception("Запись в базу не удалась.");
            }
            //  update tokens set access_tokens = access_tokens || '{"65a4b":"c106908"}'::jsonb where login = 'fifa22';
            return ["status" => "ok",
                "data" => $user,
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
