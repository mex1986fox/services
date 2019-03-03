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
            throw new \Exception("Не соответствует типу TokenStructure", 1);
        }

        if (!isset($this->tokenKey)) {
            throw new \Exception("Не установлен ключ токена", 1);
        }

        $this->token = $token;
        $checkToken = openssl_decrypt(str_replace(' ', '+', $token->getSignature()), $token->getAlg(), hex2bin($this->tokenKey), 0, hex2bin($this->tokenKey));
        if ($checkToken == $token->getHP()) {
            return true;
        }
        return false;
    }
    public function isValidLifeTime()
    {
        $tokenLifeTime = $this->token->getLifeTime();
        if (time() < $tokenLifeTime) {
            return true;
        }
        return false;
    }

}
