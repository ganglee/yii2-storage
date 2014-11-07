<?php
namespace callmez\storage\adapters;

use Yii;
use callmez\file\system\adapters\Qiniu as QiniuAdapter;

class Qiniu extends QiniuAdapter
{
    public $uploaderClass = 'callmez\storage\uploaders\Qiniu';
}