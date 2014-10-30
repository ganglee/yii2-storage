<?php
namespace callmez\storage;

use yii\base\Component;

abstract class BaseStorage extends Component
{
    abstract public function upload();
    abstract public function isUpload();
    abstract public function uploadValidate();
    abstract public function saveFileByUrl($url, $saveName = null);
    abstract public function saveFileByPath($path, $saveName = null);
    abstract public function saveFileByContent($content, $saveName = null);
    
}