<?php
namespace App\Services\Validator;

class ValidatorFactory
{

    protected $validators = [];

    public function addValidator($nameValidator)
    {
        if (!array_key_exists($nameValidator, $this->validators)) {
            $nspace = "\\Zend\\Validator\\$nameValidator";
            if (!class_exists($nspace)) {
                $nspace = "\\App\\Services\\Validator\\$nameValidator";
            }
            $this->validators[$nameValidator] = new $nspace();
        }
    }

    public function __get($nameValidator)
    {
        $this->addValidator($nameValidator);
        return $this->validators[$nameValidator];
    }
}
