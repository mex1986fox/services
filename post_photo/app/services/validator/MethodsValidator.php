<?php
namespace App\Services\Validator;

use \Zend\Validator\AbstractValidator;

// // организовать цепь валидаторов
// [
//     "nameValidator1" =>
//     [
//         ["name_param1", $p["param1"], ['min' => 1, 'max' => 16]],
//         ["name_param2", $p["param2"], ['min' => 1, 'max' => 16]],
//     ],
// ];
// // пример
// [
//     "isNumeric" =>
//     [
//         ["post_id", $p["post_is"], ['min' => 1, 'max' => 16]],
//         ["user_id", $p["user_id"], ['min' => 1, 'max' => 6]]
//     ],
//     "empty" =>
//     [
//         ["post_id", $p["post_is"]],
//         ["user_id", $p["user_id"]]
//     ],
// ];

class MethodsValidator extends AbstractValidator
{
    protected $exceptions;
    protected $container;
    protected $validators;
    public function __construct($container)
    {
        $this->container = $container;
        $this->validators = $this->container['validators'];
    }
    public function isValid($scheme)
    {
        foreach ($scheme as $nameValidator => $params) {
            // вызываем методы валидации
            call_user_method_array([$this, $nameValidator], $params);
        }
    }
    public function pushExc($name, $description)
    {
        if (empty($this->exceptions[$name])) {
            $this->exceptions[$name] = $description;
        }
    }

    function empty($name, $value) {
        if (empty($value)) {
            $this->pushExc($name, "Не указан.");
        }
    }
    public function isNumeric($name, $value)
    {
        if (!is_numeric($value)) {
            $this->pushExc($name, "Не соответствует типу Numeric.");
        }
    }
    public function isArray($name, $value)
    {
        if (!is_array($value)) {
            $this->pushExc($name, "Не соответствуе типу Array.");
        }
    }
    function token ($name, $value){
        $tokenSKey = $this->container['services']['token']['key_access_token'];
        $vToken = $this->validators->TokenValidator;
        $vToken->setKey($tokenSKey);
        if (!$vToken->isValid($value)) {
            $this->pushExc($name, "Токен не дествителен.");
        }
    }

}
