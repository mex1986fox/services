<?php
namespace App\Services\Validator;

use \Zend\Validator\AbstractValidator;

class ImgValidator extends AbstractValidator
{
    protected $return = ["error" => false, "massege" => null];
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function isValid($file)
    {
        //проверить ошибки возникшие при загрузке файла
        if ($file['error'] != 0) {
            $this->return["error"] = true;
            $this->return["massege"] = "Возникла непредвиденная ошибка при загрузке. " . $file['name'];
            return $this->return;
        }
        //проверить заголовке запроса
        if ($file['type'] != "image/jpeg") {
            $this->return["error"] = true;
            $this->return["massege"] = "Заголовок запроса не соотвертствует image/jpeg. " . $file['name'];
            return $this->return;
        }
        $imageinfo = getimagesize($file['tmp_name']);
        if ($imageinfo['mime'] != 'image/jpeg' && $imageinfo['mime'] != 'image/pjpeg') {
            $this->return["error"] = true;
            $this->return["massege"] = "Mime тип не соотвертствует image/gif. " . $file['name'];
            return $this->return;
        }
        // проверить расширение после точки
        $blacklist = array(".jpg", ".jpeg", ".jpe", ".jfif");
        $flagBL = false;
        foreach ($blacklist as $item) {
            if (preg_match("/$item\$/i", $file['name'])) {
                $flagBL = true;
                break;
            }
        }
        if ($flagBL == false) {
            $this->return["error"] = true;
            $this->return["massege"] = "Расширение не соотвертствует .jpg, .jpeg, .jpe, .jfif. " . $file['name'];
            return $this->return;
        }
        $max_file_size = 1024 * 1024 * 5;
        if ($file["size"] > $max_file_size) {
            return "Размер файла превышает 5 Mb. " . $file['name'];
            return $this->return;
        }
        return $this->return;
    }

}
//update tokens set access_tokens = '[]'::jsonb where user_id = 1 returning *;
