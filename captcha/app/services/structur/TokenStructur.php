<?php
namespace App\Services\Structur;

class TokenStructur
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
            throw new \Exception("Не определена сигнатура токена.", 1);
        }
        if (!isset($expToken[1])) {
            throw new \Exception("Не определена полезная нагрузка токена.", 1);
        }
        if (!isset($expToken[0])) {
            throw new \Exception("Не определен заголовок токена.", 1);
        }
        $this->tokenSignature = $expToken[2];
        $this->tokenHP = $expToken[0] . "." . $expToken[1];
        $this->tokenPayload = json_decode(base64_decode($expToken[1]));
        $this->tokenHeader = json_decode(base64_decode($expToken[0]));
        if (!isset($this->tokenHeader->alg)) {
            throw new \Exception("Не указан алгоритм шифрования.", 1);
        }
        if (!isset($this->tokenHeader->typ)) {
            throw new \Exception("Не указан тип токена.", 1);
        }
        if (!isset($this->tokenPayload->userID)) {
            throw new \Exception("Не указан id пользователя в токене.", 1);
        }
        if (!isset($this->tokenPayload->exp)) {
            throw new \Exception("Не указан срок действия токена.", 1);
        }
        if (!isset($this->tokenPayload->iat)) {
            throw new \Exception("Не указано время создания токена.", 1);
        }

    }
    public function initAccessToken($userId)
    {
        // создаем токены доступа
        // alg - алгоритм шифрования
        // exp – дата истечения срока действия
        // iat – время создания
        $TokenHeader = '{"alg":"AES256","typ":"JWT"}';
        $TokenIat = time(); //время создания токена
        $TokenExp = (time() + (30 * 60)); //время смерти токена после которого он не актуален
        $TokenPayload = '{"userID": ' . $userId . ',"iat": ' . $TokenIat . ', "exp":' . $TokenExp . '}';
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
    public function initRefreshToken($userId)
    {
        // создаем токены доступа
        // alg - алгоритм шифрования
        // exp – дата истечения срока действия
        // iat – время создания
        $TokenHeader = '{"alg":"AES256","typ":"JWT"}';
        $TokenIat = time(); //время создания токена
        $TokenExp = (time() + (30 * 24 * 60 * 60)); //время смерти токена после которого он не актуален
        $TokenPayload = '{"userID": ' . $userId . ',"iat": ' . $TokenIat . ', "exp":' . $TokenExp . '}';
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
    public function getUserID()
    {
        return $this->tokenPayload->userID;
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
