<?php
namespace App\Models\Api\Captcha;

use \App\Services\Structur\CaptchaTokenStructur;

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
        // создает новыю каптчу
        try {
            
            //новое поле в базе
            $db = $this->container['db'];
            $q = "insert into captcha (token) values ('{}'::jsonb) returning captcha_id";
            $tokenDB = $db->query($q, \PDO::FETCH_ASSOC)->fetch();
            
            //формируем токен для базы
            $captchaId = $tokenDB["captcha_id"];
            $tokenStructur = new CaptchaTokenStructur();
            $tokenStructur->initToken($captchaId);

            // формируем каптчу

            $q = "update captcha set token='{\"{$tokenStructur->getSignature()}\":\"{$tokenStructur->getSecretKey()}\"}'::jsonb where captcha_id=" . $captchaId;
            $tokenDB = $db->query($q, \PDO::FETCH_ASSOC)->fetch();

            return ["status" => "ok",
                "data" => [
                    "token"=>$tokenStructur->getToken(),
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
