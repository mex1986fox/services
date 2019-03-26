<?php
namespace App\Services\Validator;

use \App\Services\Structur\TokenStructur;
use \Zend\Validator\AbstractValidator;

class TokenValidator extends AbstractValidator
{
    protected $token;
    protected $tokenKey;

    public function setKey($tokenKey)
    {
        $this->tokenKey = $tokenKey;
    }
    public function isValid($token)
    {
        if (!$token instanceof TokenStructur) {
            throw new \Exception("Не соответствует типу TokenStructure.", 1);
            return false;
        }
        $this->token = $token;
        if (!isset($this->tokenKey)) {
            throw new \Exception("Не установлен ключ токена.", 1);
            return false;
        }
        // проверяем время жизни
        if (time() > $token->getLifeTime()) {
            throw new \Exception("Время жизни токена истекло.", 1);
            return false;
        }

        $this->token = $token;
        $checkToken = openssl_decrypt(str_replace(' ', '+', $token->getSignature()), $token->getAlg(), hex2bin($this->tokenKey), 0, hex2bin($this->tokenKey));

        if ($checkToken == $token->getHP()) {
            return true;
        }else{
            throw new \Exception("Не валидный токен.", 1);
            return false;
        }
        return false;
    }
}
//update tokens set access_tokens = '[]'::jsonb where user_id = 1 returning *;
