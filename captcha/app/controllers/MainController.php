<?php
namespace App\Controllers;

use \Slim\Container;

class MainController
{
    protected static $stContainer;
    protected $container;

    public function __construct()
    {
        $this->container = self::$stContainer;
    }

    public static function installContainer(Container $container)
    {
        self::$stContainer = $container;
    }
}
