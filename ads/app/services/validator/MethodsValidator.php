<?php
namespace App\Services\Validator;

use \App\Services\Structur\TokenStructur;
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
    protected $exceptions = [];
    protected $container;
    protected $validators;
    public function __construct($container)
    {
        $this->container = $container;
        $this->validators = $this->container['validators'];
    }
    public function isValid($scheme)
    {
        foreach ($scheme as $nameValidator => $valids) {
            foreach ($valids as $params) {
                // вызываем методы валидации
                call_user_func_array(array($this, $nameValidator), $params);
            }
        }
        if (!empty($this->exceptions)) {
            return false;
        };
        return true;
    }
    public function isValidFilled($scheme)
    {
        foreach ($scheme as $nameValidator => $valids) {
            foreach ($valids as $params) {
                // вызываем методы валидации если значение не пустое
                if (isset($params[1][$params[0]])) {
                    $val = $params[1][$params[0]];
                    $params[1] = $val;
                    call_user_func_array(array($this, $nameValidator), $params);
                }
            }
        }
        if (!empty($this->exceptions)) {
            return false;
        };
        return true;
    }
    public function getExceptions()
    {
        return $this->exceptions;
    }
    public function pushExc($name, $description)
    {

        if (empty($this->exceptions[$name])) {
            $this->exceptions[$name] = $description;
        }
    }
    public function emptyParamsFilled($name, $p)
    {

        if (empty($p)) {
            $this->pushExc($name, "Пустое значение.");
        }
        if (!empty($p) && is_array($p)) {

            foreach ($p as $key => $value) {
                $fEmp = true;
                if (!empty($value)) {
                    $fEmp = false;
                }
                if ($fEmp == true) {
                    $this->pushExc($name, "Array с пустыми значениями");
                }
            }
        }
    }
    public function emptyParams($name, $p)
    {

        if (empty($p[$name])) {
            $this->pushExc($name, "Пустое значение.");
        }
        if (!empty($p[$name]) && is_array($p[$name])) {

            foreach ($p[$name] as $key => $value) {
                $fEmp = true;
                if (!empty($value)) {
                    $fEmp = false;
                }
                if ($fEmp == true) {
                    $this->pushExc($name, "Array с пустыми значениями");
                }
            }
        }
    }
    public function isSetParams($name, $p)
    {
        if (!isset($p[$name])) {
            $this->pushExc($name, "Не указан.");
        }
    }
    public function isNumeric($name, $value)
    {
        if (!is_numeric($value)) {
            $this->pushExc($name, "Не соответствует типу Numeric.");
        }
    }
    public function isBool($name, $value)
    {
        if (!is_bool((Boolean) $value)) {
            $this->pushExc($name, "Не соответствует типу Boolean.");
        }
    }
    public function isArray($name, $value)
    {
        if (!is_array($value)) {
            $this->pushExc($name, "Не соответствуе типу Array.");
        }
    }
    public function strLen($name, $value, $params = ["min" => 0, "max" => 0])
    {
        $vStLen = $this->validators->StringLength;
        $vStLen->setMin($params["min"]);
        $vStLen->setMax($params["max"]);
        if (!$vStLen->isValid($value)) {
            $this->pushExc($name, "Допустимое количество знаков от " . $params["min"] . " до " . $params["max"]);
        }
    }
    public function uri($name, $value)
    {
        $v = $this->validators->Uri;
        $v->setAllowRelative(false);
        $v->setAllowAbsolute(true);
        if (!$v->isValid($value)) {
            $this->pushExc($name, "Не соответствует типу Uri");
        }
    }
    public function isFloat($name, $value)
    {
        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            $this->pushExc($name, " Не соответствует типу Float");
        }

    }
    public function isInt($name, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->pushExc($name, " Не соответствует типу Integer");
        }

    }
    public function between($name, $value, $params = ["min" => 0, "max" => 0])
    {
        if ($value < $params["min"] || $value > $params["max"]) {
            $this->pushExc($name, "Допустимое значение от " . $params["min"] . " до " . $params["max"]);
        }

    }
    public function isAccessToken($name, $value)
    {
        try {

            $accessToken = $value;
            $tokenStructur = new TokenStructur($this->container);
            $tokenStructur->setToken($accessToken);
            // проверяем параметры
            $valid = $this->container['validators'];
            $tokenSKey = $this->container['services']['token']['key_access_token'];
            $vToken = $valid->TokenValidator;
            $vToken->setKey($tokenSKey);
            if (!$vToken->isValid($tokenStructur)) {
                $this->pushExc($name, "Не действителен.");
            }
        } catch (RuntimeException | \Exception $e) {
            $this->pushExc($name, $e->getMessage());
        }

    }

}
