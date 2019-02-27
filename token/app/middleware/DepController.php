<?php
namespace App\Middleware;

use App\Controllers\MainController;

/*
 * Подключает зависимости к главному контроллеру
 */

class DepController
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function __invoke($request, $response, $next)
    {
        MainController::installContainer($this->container);
        return $next($request, $response);
     
    }
}
