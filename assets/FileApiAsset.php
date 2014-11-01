<?php

namespace callmez\storage\assets;

use Yii;
use yii\web\View;
use yii\helpers\Json;
use yii\web\AssetBundle;

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
    public $cssCode = "/* Jquery File Upload */
.file-input {
    position: relative;
    overflow: hidden;
}
.file-input input {
    position: absolute;
    top: 0;
    right: 0;
    margin: 0;
    opacity: 0;
    -ms-filter: 'alpha(opacity=0)';
    font-size: 200px;
    direction: ltr;
    cursor: pointer;
}
/* Fixes for IE < 8 */
@media screen\9 {
    .file-input input {
        filter: alpha(opacity=0);
        font-size: 100%;
        height: 100%;
    }
}";


    public function registerAssetFiles($view)
    {
        if ($this->useJquery) {
            $this->js[] = 'jquery.fileapi.js';
        }
        $view = Yii::$app->controller->getView();
        //FileAPI 基本设置,必须放在当前js加载前头
        $view->registerJs('window.FileAPI = ' . Json::encode(array_merge([
                'debug' => YII_DEBUG ? 1: 0,
                'staticPath' => Yii::getAlias($this->baseUrl)
            ], $this->settings)) . ';', View::POS_HEAD);
        $this->cssCode !== null && $view->registerCss($this->cssCode);
        parent::registerAssetFiles($view);
    }
}