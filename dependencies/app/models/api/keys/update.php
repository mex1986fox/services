<?php
namespace App\Models\Api\Keys;

class update
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
        // обновляет ключи у сервисов
        try {
            $services = $this->container['services'];
            $ivlen = openssl_cipher_iv_length('AES256');
            $key_access_token = openssl_random_pseudo_bytes($ivlen);
            $key_refresh_token = openssl_random_pseudo_bytes($ivlen);
            $key_captcha_token = openssl_random_pseudo_bytes($ivlen);
            foreach ($services as $key => $service) {
                 $services[$key]["key_access_token"] = bin2hex($key_access_token);
                 $services[$key]["key_refresh_token"] =bin2hex($key_refresh_token);
                 $services[$key]["key_captcha_token"] = bin2hex($key_captcha_token);
             }
            // сохраняем в конфиг
            file_put_contents(__DIR__ . '/../../../configs/services-config.json', json_encode($services));
            return ["status" => "ok",
                "data" => null,
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
