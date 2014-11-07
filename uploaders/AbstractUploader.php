<?php
namespace callmez\storage\uploaders;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use callmez\storage\FileSystem;
use callmez\storage\UploaderInterface;

abstract class AbstractUploader extends Object implements UploaderInterface
{
    /**
     * 上传图片域, 默认值为file, 如$_FILES[$this->fileKey]
     * @var string
     */
    public $fileKey;
    /**
     * 上传所依附的存储系统
     * @var object
     */
    public $fileSystem;

    public static function getInstance(FileSystem $fileSystem, $config = [])
    {
        if (!property_exists($fileSystem->getAdapter(), 'uploaderClass')) {
            throw new InvalidConfigException("The file system adapter 'uploaderClass' property is missing.");
        }
        $config = array_merge([
            'class' => $fileSystem->getAdapter()->uploaderClass,
            'fileSystem' => $fileSystem,
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