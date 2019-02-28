<?php
namespace App\Services\Validator;

use \Zend\validator\AbstractValidator;

class TrustedServicesValidator extends AbstractValidator
{
    public function isValid(Array $ips)
    {
        // проверяет является ли входной запрос от доверенного ip
        foreach ($ips as $key => $ip) {
            if($_SERVER['REMOTE_ADDR']==$ip){
                return true;
            }
        }
        return false;
    }
}
