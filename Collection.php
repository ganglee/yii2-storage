<?php
namespace callmez\storage;

use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use callmez\file\system\Collection as FileSystemCollection;

class Collection extends FileSystemCollection
{
    /**
     * 默认文件存储
     * @var string
     */
    public $defaultFileSystem;

    public function init()
    {
        if ($this->defaultFileSystem === null) {
            throw new InvalidConfigException("The 'defaultFileSystem' property must be set.");
        }
    }

    /**
     * defaultFileSystem支持
     * @param array $arguments
     * @return array
     * @throws InvalidCallException
     * @throws InvalidParamException
     */
    public function filterPrefix(array $arguments)
    {
        if (empty($arguments)) {
            throw new InvalidCallException('At least one argument needed');
        }
        $path = array_shift($arguments);
        if (!is_string($path)) {
            throw new InvalidParamException('First argument should be a string');
        }
        if (!preg_match('#^[a-zA-Z0-9]+\:\/\/.*#', $path)) {
            $prefix = $this->defaultFileSystem;
        } else {
            list ($prefix, $path) = explode('://', $path, 2);
        }

        array_unshift($arguments, $path);
        return array($prefix, $arguments);
    }

    /**
     * 默认文件存储支持
     * @param string $id
     * @return mixed
     */
    public function get($id = null)
    {
        $id === null && $id = $this->defaultFileSystem;
        return parent::get($id);
    }

    /**
     * 创建adapter,增加文件操作接口判断
     * @param $id
     * @param $config
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function create($id, $config)
    {
        $object = parent::create($id, $config);
        if (!($object->getAdapter() instanceof FileProcessInterface)) {
            throw new InvalidConfigException("The adapter '{$id}' must implement of \\callmez\\storage\\FileProcessInterface");
        }
        return $object;
    }
}