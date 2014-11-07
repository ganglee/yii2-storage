<?php
namespace callmez\storage\uploaders;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use callmez\storage\FileSystem;
use callmez\storage\UploaderInterface;

abstract class AbstractUploader extends Object implements UploaderInterface
{
    public $fileName;
    public $fileSystem;
    public static function getInstance($fileName, FileSystem $fileSystem, $config = [])
    {
        if (!property_exists($fileSystem->getAdapter(), 'uploaderClass')) {
            throw new InvalidConfigException("The file system adapter property 'uploaderClass' is missing.");
        }
        $config = array_merge([
            'class' => $fileSystem->getAdapter()->uploaderClass,
            'fileSystem' => $fileSystem,
            'fileName' => $fileName
        ], $config);
        return Yii::createObject($config);
    }

    private $_error;
    public function hasError()
    {
        return $this->_error !== null;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function setError($error)
    {
        $this->_error = $error;
    }
}