<?php
namespace callmez\storage\helpers;

use Yii;

class StorageHelper
{
    /**
     * 获取文件全路径, 可返回CDN地址
     * @param $path
     * @return string
     */
    public static function getAssetUrl($path)
    {
        if (strpos($path, 'http://') === false && strpos($path, 'https://') === false) {
            return Yii::getAlias('web') . $path;
        }
        return $path;
    }


}