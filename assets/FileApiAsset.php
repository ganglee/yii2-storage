<?php

namespace yii\storage\assets;

use Yii;
use yii\web\View;
use yii\helpers\Json;
use app\components\AssetBundle;

class FileApiAsset extends AssetBundle
{
    public $useJquery = true;
    public $settings = [
        'withCredentials' => false // 默认开启跨域
    ];
    public $sourcePath = '@bower/jquery.fileapi';
    public $js = [
        'FileAPI/FileAPI.min.js',
        'FileAPI/FileAPI.exif.js'
    ];
    public $depends = [
        'app\assets\AppAsset'
    ];

    public function registerAssetFiles($view)
    {
        if ($this->useJquery) {
            $this->js[] = 'jquery.fileapi.js';
        }

        //FileAPI 基本设置,必须放在当前js加载前头
        Yii::$app->controller->getView()->registerJs('window.FileAPI = ' . Json::encode(array_merge([
                'debug' => YII_DEBUG ? 1: 0,
                'staticPath' => Yii::getAlias($this->baseUrl)
            ], $this->settings)) . ';', View::POS_HEAD);

        parent::registerAssetFiles($view);
    }
}