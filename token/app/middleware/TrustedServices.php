<?php
namespace App\Middleware;

/*
 * Накладывает проверку на IP на заданные маршруты
 * если запрос поступил от доверенного IP
 * тогда запрос не блокируется
 * в обратном случае выкидывается предупреждение "Нет доверия к IP."
 */

class TrustedServices
{
    protected $trusted_services;
    public function __construct($trusted_services)
    {
        $this->trusted_services = $trusted_services;
    }
    public function __invoke($request, $response, $next)
    {
        //проверяем стоит ли для данного маршрута использовать проверку
        $checkFlag = false;
        foreach ($this->trusted_services['paths'] as $key => $path) {
            if (stripos($request->getUri()->getPath(), $path) !== false) {
                $checkFlag = true;
            }
        }

        if ($checkFlag == true) {
            //проверяем входит ли в группу доверенных адресов
            foreach ($this->trusted_services['ip'] as $key => $ip) {
                if ($_SERVER['REMOTE_ADDR'] == $ip) {
                    // если входит тогда прокидываем запрос дальше
                    return $next($request, $response);
                }
            }
        }else{
            return $next($request, $response);
        }

        $answer = [
            "status" => "except",
            "data" => ["massege" => "Нет доверия к IP."],
        ];
        $response = $response->withJson($answer, 403);
        return $response;
    }
}
