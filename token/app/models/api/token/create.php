<?php
namespace App\Models\Api\Token;

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
        // создает новое поле в базе token
        // записывает туда id пользователя логин и пароль
        // данная операция доступна только доверенным сервисам
        // от внешнего мира должна быть скрыта
        return [
            "status" => "ok",
            "data" => null,
        ];
    }
}
