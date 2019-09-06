<?php
namespace App\Services\Validator;

class ValidatorFactory
{

    protected $validators = [];
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function addValidator($nameValidator, $arguments = null)
    {
        if (!array_key_exists($nameValidator, $this->validators)) {
            $nspace = "\\Zend\\Validator\\$nameValidator";
            if (!class_exists($nspace)) {
                $nspace = "\\App\\Services\\Validator\\$nameValidator";
                $this->validators[$nameValidator] = new $nspace($this->container);
            } else {
                if ($arguments != null) {
                    $this->validators[$nameValidator] = new $nspace($arguments);
                } else {
                    $this->validators[$nameValidator] = new $nspace();
                }

            }

        }
    }

    public function __get($nameValidator)
    {
        $this->addValidator($nameValidator);
        return $this->validators[$nameValidator];
    }
    public function __call($nameValidator, $arguments)
    {
        $this->addValidator($nameValidator, $arguments);
        return $this->validators[$nameValidator];
    }
}
