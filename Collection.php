<?php
namespace yii\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * 存储集合
 * @package yii\qiniu
 */
class Collection extends Component
{
    /**
     * 默认存储
     * @var string
     */
    public $defaultStorage = 'local';
    /**
     * 可使用的存储列表
     * @var array
     */
    private $_bin = [
        'local' => [
            'class' => 'yii\storage\bin\LocalStorage'
        ],
    ];
    /**
     * 存储列表
     * @var array
     */
    private $_storages = [];

    /**
     * 设置存储列表
     * @param array $config
     */
    public function setBin(array $config)
    {
        $this->_bin = array_merge($this->_bin, $config);
    }

    /**
     * 获取存储列表
     * @return array
     */
    public function getBin()
    {
        return $this->_bin;
    }

    /**
     * 检查是有指定存储存在
     * @param $name 存储名
     * @return bool
     */
    public function hasStorage($name)
    {
        return array_key_exists($name, $this->_storages);
    }

    /**
     * 获取指定的存储
     * @param string $name 存储名
     * @return object 返回存储实例
     * @throws \yii\base\InvalidParamException
     */
    public function getStorage($name = null)
    {
        $name === null && $name = $this->defaultStorage;
        if ($this->hasStorage($name)) {
            return $this->_storages[$name];
        } elseif (array_key_exists($name, $this->_bin)) {
            return $this->_storages[$name] = $this->createStorage($name, $this->_bin[$name]);
        } else {
            throw new InvalidParamException("Unknown storage {$name}");
        }
    }

    /**
     * 创建存储
     * @param $name
     * @param $config
     * @return object
     */
    public function createStorage($name, $config)
    {
        $config['name'] = $name;
        return Yii::createObject($config);
    }
}