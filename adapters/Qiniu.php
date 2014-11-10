<?php
namespace callmez\storage\adapters;

use Yii;
use callmez\file\system\adapters\Qiniu as QiniuAdapter;
use callmez\storage\FileProcessInterface;

class Qiniu extends QiniuAdapter implements FileProcessInterface
{
    public $uploaderClass = 'callmez\storage\uploaders\Qiniu';

    public function getThumbnail($path, array $options)
    {
        $path .= '?imageView/2/';
        isset($options['width']) && $path .= 'w/' . $options['width'];
        isset($options['height']) && $path .= 'h/' . $options['height'];
        return $path;
    }
}