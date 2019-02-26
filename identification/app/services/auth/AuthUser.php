<?php
namespace App\Services\Auth;

class AuthUser
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function Authed()
    {
        if (!empty($_SESSION["user_id"])) {
            return true;
        }
        return false;

    }
}
