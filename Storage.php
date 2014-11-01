<?php
namespace callmez\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

class Storage extends Component
{
    public $defaultStorage;

    public function init()
    {
        if ($this->defaultStorage === null) {
            throw new InvalidConfigException("'defaultStorage' property must be set.");
        }
    }
}