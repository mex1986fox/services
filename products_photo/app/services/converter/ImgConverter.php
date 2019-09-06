<?php
namespace App\Services\Converter;

class ImgConverter
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function isTransform($file, int $heignt)
    {
        //определяем тип файла
        $img = imageCreateFromJPEG($file);
        $rwidth = imagesx($img);
        $rheight = imagesy($img);
        //задаем размеры для нового фото
        $newHeight = $rheight < $heignt ? $rheight : $heignt;
        $newWidth = round(($newHeight * $rwidth / $rheight), 0);
        //создаем новое фото
        $newImage = @imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $img, 0, 0, 0, 0, $newWidth, $newHeight, $rwidth, $rheight);
        //imagefilter($img, IMG_FILTER_PIXELATE, 4, true);
        return $newImage;
    }
}
