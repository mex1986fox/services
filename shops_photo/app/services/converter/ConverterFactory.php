<?php
namespace App\Services\Converter;

class ConverterFactory
{

    protected $Converters = [];
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function addConverter($nameConverter)
    {
        if (!array_key_exists($nameConverter, $this->Converters)) {
            $nspace = "\\App\\Services\\Converter\\$nameConverter";
            $this->Converters[$nameConverter] = new $nspace($this->container);
        }
    }

    public function __get($nameConverter)
    {
        $this->addConverter($nameConverter);
        return $this->Converters[$nameConverter];
    }
}
