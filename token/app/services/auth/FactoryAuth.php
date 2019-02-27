<?php
namespace App\Services\Auth;

class FactoryAuth
{

    protected $auths = [];
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function addAuth($nameAuth)
    {
        if (!array_key_exists($nameAuth, $this->auths)) {
            $nspace = "\\App\\Services\\Auth\\$nameAuth";
            $this->auths[$nameAuth] = new $nspace($this->container);
        }
    }

    public function __get($nameAuth)
    {
        $this->addAuth($nameAuth);
        return $this->auths[$nameAuth];
    }
}
