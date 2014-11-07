<?php
namespace callmez\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class Storage extends Component
{
    public function init()
    {
        if (!Yii::$app->has('fileSystemCollection')) {
            throw new InvalidConfigException("The component 'storage' need dependency on component 'fileSystemCollection'.");
        }
    }

    public function loadFileSystem()
    {

    }
}