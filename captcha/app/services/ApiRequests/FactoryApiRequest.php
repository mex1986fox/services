<?php
namespace App\Services\ApiRequests;

class FactoryApiRequest
{

    protected $requests = [];
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function addRequest($nameRequest)
    {
        if (!array_key_exists($nameRequest, $this->requests)) {
            $nspace = "\\App\\Services\\ApiRequests\\$nameRequest";
            $this->requests[$nameRequest] = new $nspace($this->container);
        }
    }

    public function __get($nameRequest)
    {
        $this->addRequest($nameRequest);
        return $this->requests[$nameRequest];
    }
}
