<?php
namespace callmez\storage\adapters;

use Yii;
use callmez\file\system\adapters\Local as LocalAdapter;

class Local extends LocalAdapter
{
    public $uploaderClass = 'callmez\storage\uploaders\Local';
}