<?php
namespace App\Services\Validator;

use \App\Services\Structur\CaptchaTokenStructur;
use \Zend\Validator\AbstractValidator;

class TokenCaptchaValidator extends AbstractValidator
{
    protected $token;
    protected $tokenKey;
    public function setKey($tokenKey)
    {
        $this->tokenKey = $tokenKey;
    }
    public function isValid($token)
    {
        if (!$token instanceof CaptchaTokenStructur) {
            throw new \Exception("Не соответствует типу TokenStructure", 1);
        }

        if (!isset($this->tokenKey)) {
            throw new \Exception("Не установлен ключ токена", 1);
        }
        $this->token = $token;
        if (time() > $this->token->getLifeTime()) {
            throw new \Exception("Истекло время жизни токена.", 1);
            return false;
        }

        if ($this->token->getStatus() == false) {
      
            throw new \Exception("Каптча не пройдена.", 1);
            return false;
        }

        $this->token = $token;
        $checkToken = openssl_decrypt(str_replace(' ', '+', $token->getSignature()), $token->getAlg(), hex2bin($this->tokenKey), 0, hex2bin($this->tokenKey));
        if ($checkToken == $token->getHP()) {
            return true;
        } else {
            throw new \Exception("Не валидный токен.", 1);
            return false;
        }

        return false;
    }
}
