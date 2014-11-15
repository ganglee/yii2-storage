<?php
namespace callmez\storage\helpers;

use Yii;

class UploadHelper
{
    public static function generateUniquePath($name, $template = null, $uniqueKey = null)
    {
        $template = '/' . ltrim($template ?: implode('/', [
            date('Y'),
            date('m'),
            date('d'),
            '{key}.' . (pathinfo($name, PATHINFO_EXTENSION) ?: 'temp')
        ]), '/');
        return strtr($template, [
            '{key}' => $uniqueKey ?: uniqid()
        ]);
    }
}