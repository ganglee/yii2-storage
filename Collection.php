<?php
namespace callmez\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

class Collection extends Component
{
    private $_storages = [];

    /**
     * 可使用的存储列表
     * @var array
     */
    private $_bin = [];

    /**
     * 设置存储列表
     * @param array $config
     */
    public function setBin(array $config)
    {
        $this->_bin = array_merge($config, [
            'local' => [
                'class' => 'callmez\storage\bin\LocalStorage'
            ]
        ]);
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
     * 判断存储器是否存在
     * @param $name
     * @return bool
     */
    public function hasStorage($name)
    {
        return array_key_exists($name, $this->_storages);
    }

    /**
     * 设置存储器
     * @param $name
     * @param $config
     */
    public function setStroage($name, BaseStorage $storage)
    {
        $this->_storages[$name] = $storage;
    }

    /** 获取存储器
     * @param string $name
     * @return mixed
     * @throws \yii\base\InvalidParamException
     */
    public function getStroage($name = 'local')
    {
        if (!$this->hasStorage($name) && array_key_exists($name, $this->_bin)) {
            $this->setStroage($name, $this->createStorage($name, $this->_bin[$name]));
        } else {
            throw new InvalidParamException("Unknown storage {$name}");
        }
        return $this->_storages[$name];
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