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
    protected $container;
    protected $path;
    protected $services;
    public function __construct($container)
    {
        $this->container = $container;
        $this->path = $container["closed_records"]['paths'];
        $this->services =$container["services"];  

    }
    public function __invoke($request, $response, $next)
    {
        //проверяем стоит ли для данного маршрута использовать проверку
        $checkFlag = false;
        foreach ($this->path as $key => $path) {
            if (stripos($request->getUri()->getPath(), $path) !== false) {
                $checkFlag = true;
            }
        }

        if ($checkFlag == true) {
            //проверяем входит ли в группу доверенных адресов
            foreach ($this->services as $key => $serv) {
                if ($_SERVER['REMOTE_ADDR'] == $serv["ip"]) {
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
