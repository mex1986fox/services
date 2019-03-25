<?php
namespace App\Services\Structur;

class CaptchaTokenStructur
{
    protected $token;
    protected $tokenSignature;
    protected $tokenPayload;
    protected $tokenHeader;
    protected $tokenHP;
    protected $tokenSecretKey;

    public function setToken(string $tStructure)
    {
        $this->token = $tStructure;
        //извлечь TokenSignature, TokenPayload, TokenHeader
        $expToken = explode(".", $tStructure);
        if (!isset($expToken[2])) {
            throw new \Exception("Не определена сигнатура каптчи.", 1);
        }
        if (!isset($expToken[1])) {
            throw new \Exception("Не определена полезная нагрузка каптчи.", 1);
        }
        if (!isset($expToken[0])) {
            throw new \Exception("Не определен заголовок каптчи.", 1);
        }
        $this->tokenSignature = $expToken[2];
        $this->tokenHP = $expToken[0] . "." . $expToken[1];
        $this->tokenPayload = json_decode(base64_decode($expToken[1]));
        $this->tokenHeader = json_decode(base64_decode($expToken[0]));
        if (!isset($this->tokenHeader->alg)) {
            throw new \Exception("Не указан алгоритм шифрования.", 1);
        }
        if (!isset($this->tokenHeader->typ)) {
            throw new \Exception("Не указан тип каптчи.", 1);
        }
        if (!isset($this->tokenPayload->captchaID)) {
            throw new \Exception("Не указан id номер каптчи.", 1);
        }
        if (!isset($this->tokenPayload->exp)) {
            throw new \Exception("Не указан срок действия каптчи.", 1);
        }
        if (!isset($this->tokenPayload->iat)) {
            throw new \Exception("Не указано время создания каптчи.", 1);
        }

    }
    public function initToken($captchaId)
    {
        // создаем токены доступа
        // alg - алгоритм шифрования
        // exp – дата истечения срока действия
        // iat – время создания
        $TokenHeader = '{"alg":"AES256","typ":"JWT"}';
        $TokenIat = time(); //время создания каптчи
        $TokenExp = (time() + (30 * 60)); //время смерти каптчи после которого он не актуален
        $TokenPayload = '{"captchaID": ' . $captchaId . ',"iat": ' . $TokenIat . ', "exp":' . $TokenExp . '}';
        $TokenHPEncode = base64_encode($TokenHeader) . "." . base64_encode($TokenPayload);
        $ivlen = openssl_cipher_iv_length('AES256');
        $TokenSecretKey = openssl_random_pseudo_bytes($ivlen);
        $TokenSignature = openssl_encrypt($TokenHPEncode, 'AES256', $TokenSecretKey, 0, $TokenSecretKey);
        $Token = $TokenHPEncode . "." . $TokenSignature;
        $TokenSecretKeyHex = bin2hex($TokenSecretKey);
        $this->token = $Token;
        $this->tokenSignature = $TokenSignature;
        $this->tokenHP = $TokenHPEncode;
        $this->tokenPayload = json_decode($TokenPayload);
        $this->tokenHeader = json_decode($TokenHeader);
        $this->tokenSecretKey = $TokenSecretKeyHex;

    }
    public function getToken()
    {
        return $this->token;
    }
    public function getSignature()
    {
        return $this->tokenSignature;
    }
    public function getPayload()
    {
        return $this->tokenPayload;
    }
    public function getHP()
    {
        return $this->tokenHP;
    }
    public function getHeader()
    {
        return $this->tokenHeader;
    }
    public function getAlg()
    {
        return $this->tokenHeader->alg;
    }
    public function getTyp()
    {
        return $this->tokenHeader->typ;
    }
    public function getCaptchaID()
    {
        return $this->tokenPayload->captchaID;
    }
    public function getLifeTime()
    {
        return $this->tokenPayload->exp;
    }
    public function getCreatTime()
    {
        return $this->tokenPayload->iat;
    }
    public function getSecretKey()
    {
        if (isset($this->tokenSecretKey)) {
            return $this->tokenSecretKey;
        }
        return false;
    }
}
