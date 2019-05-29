<?php
namespace App\Middleware;

class CORSResponse
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function __invoke($request, $response, $next)
    {
        $newResponse = $response->withHeader('Access-Control-Allow-Origin', '*');
        $newResponse = $newResponse->withHeader('Access-Control-Max-Age', '100000');
        return $next($request, $newResponse);
    }
}
