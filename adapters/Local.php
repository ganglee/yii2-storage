<?php
namespace callmez\storage\adapters;

use Yii;
use callmez\file\system\adapters\Local as LocalAdapter;
use callmez\storage\FileProcessInterface;

class Local extends LocalAdapter implements FileProcessInterface
{
    public $uploaderClass = 'callmez\storage\uploaders\Local';

    public function getThumbnail($path, $width, $height, $config = null)
    {

    }
}