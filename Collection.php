<?php
namespace yii\qiniu;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * 存储集合
 * 可以设置多存储
 * @package yii\qiniu
 */
class Collection extends Component
{
    public $defaultStorage = 'local';

    private $_bin = [
        'local' => [
            'class' => 'app\components\storage\bin\LocalStorage'
        ]
    ];

    private $_straoges = [];

    public function setBin(array $config)
    {
        $this->_bin = array_merge($this->_bin, $config);
    }

    public function getBin()
    {
        return $this->_bin;
    }

    public function hasStorage($name)
    {
        return array_key_exists($name, $this->_straoges);
    }

    public function getStorage($name = null)
    {
        $name === null && $name = $this->defaultStorage;
        if ($this->hasStorage($name)) {
            return $this->_straoges[$name];
        } elseif (array_key_exists($name, $this->_bin)) {
            return $this->_straoges[$name] = $this->createStorage($name, $this->_bin[$name]);
        } else {
            throw new InvalidParamException("Unknown storage {$name}");
        }
    }

    public function createStorage($name, $config)
    {
        $config['name'] = $name;
        return Yii::createObject($config);
    }
}