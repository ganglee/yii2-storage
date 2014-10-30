<?php
namespace callmez\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

class Collection extends Component
{
    public $defaultStorage = 'local';
    private $_storages = [
        'local' => [
            'class' => 'League\Flysystem\Adapter\Local'
        ]
    ];

    public function setStorages(array $storages)
    {
        $this->_storages = $storages;
    }

    public function getStorages()
    {
        $storages = [];
        foreach ($this->_storages as $id => $storage)
        {
            $storage[$id] = $this->get($id);
        }
        return $storages;
    }

    public function get($id = null)
    {
        $id === null && $id = $this->defaultStorage;
        if (!$this->has($id)) {
            throw new InvalidParamException("Unknown storage '{$id}'.");
        }
        if (!is_object($this->_storages[$id])) {
            $this->_storages[$id] = $this->create($id, $this->_storages[$id]);
            if (!is_subclass_of($this->_storages[$id], 'callmez\storage\BaseStorage')) {
                throw new InvalidParamException("The storage '{$id}' must extend from callmez\\storage\\BaseStorage.");
            }
        }

        return $this->_storages[$id];
    }

    public function has($id)
    {
        return array_key_exists($id, $this->_storages);
    }

    public function create($id, $config)
    {
        $config['id'] = $id;
        return Yii::createObject($config);
    }
}