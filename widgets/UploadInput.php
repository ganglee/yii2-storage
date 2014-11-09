<?php
namespace callmez\storage\widgets;

use Yii;
use yii\web\View;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use callmez\storage\assets\FileApiAsset;
use callmez\storage\uploaders\AbstractUploader;

class UploadInput extends Widget
{
    public $form;
    public $model;
    public $attribute;
    public $fieldConfig = [
        'template' => "{label}\n<div class=\"input-group\">\n{input}\n<span class=\"input-group-btn\">\n<span class=\"btn btn-default file-input\">\n{text}\n{fileInput}\n</span>\n</span>\n</div>\n{preview}\n{hint}\n{error}"
    ];
    public $fileInputOptions = [];
    public $previewOptions = ['class' => 'img-thumbnail', 'width' => 45, 'height' => 45];
    /**
     * 需要渲染的节点, 只能渲染{preview}, fileInput, 其他节点需要到ActiveField实例中渲染
     * @var array
     */
    public $parts = [
        '{text}' => '选择文件'
    ];
    /**
     * @var object callmez\storage\uploaders\AbstractUploader
     */
    private $_field;

    public function init()
    {
        if ($this->form === null || is_subclass_of($this->form, ActiveForm::className())) {
            throw new InvalidConfigException("The 'form' property must be set.");
        } elseif (!($this->form instanceof ActiveForm)) {
            throw new InvalidConfigException("The 'form' property must instance of class " . ActiveForm::className() . ".");
        } elseif ($this->model === null) {
            throw new InvalidConfigException("The 'model' property must be set.");
        } elseif (!($this->model instanceof Model)) {
            throw new InvalidConfigException("The 'model' property must instance of class " . Model::className() . ".");
        } elseif ($this->attribute === null) {
            throw new InvalidConfigException("The 'attribute' property must be set.");
        } elseif (!($this->uploader instanceof AbstractUploader)) {
            throw new InvalidConfigException("The 'attribute' property must be set and instance of " . AbstractUploader::className() . ".");
        }
        // fileApi需要ID来指定作用域
        $this->fieldConfig['options'] = array_merge([
            'id' => Html::getInputId($this->model, $this->attribute) . '-wrapper',
            'class' => 'form-group'
        ], isset($this->fieldConfig['options']) ? $this->fieldConfig['options'] : []);
    }

    public function run()
    {
        if (isset($this->fieldConfig['template'])) {
            if (!isset($this->parts['{preview}'])) {
                $this->parts['{preview}'] = Html::img($this->getPreviewUrl(), $this->previewOptions);
            }
            if (!isset($this->parts['{fileInput}'])) {
                $this->parts['{fileInput}'] = Html::fileInput('file', null, $this->fileInputOptions);
            }
            $this->fieldConfig['template'] = strtr($this->fieldConfig['template'], $this->parts);
        }
        $this->_field = $this->form->field($this->model, $this->attribute, $this->fieldConfig);
        $this->registerJs();
        return $this->_field;
    }

    public function getPreviewUrl()
    {
        return Html::getAttributeValue($this->model, $this->attribute);
    }

    public $uploader;
    public $uploadSettings = [
        'autoUpload' => true // 默认开启自动上传
    ];
    public $uploadPosition = View::POS_READY;
    public $uploadKey;
    /**
     * JS上传设置, 以FileAPI上传插件为基础
     * @param $settings
     * @param int $position
     */
    public function registerJs()
    {
        $settings = $this->uploader->getUploadSettings($this->uploadSettings);
        $settings['url'] = Url::to(ArrayHelper::getValue($settings, 'url', ''), true);
        $request = Yii::$app->getRequest();
        $settings['data'] = array_merge([ // csrf验证
            $request->csrfParam => $request->getCsrfToken(),
        ], ArrayHelper::getValue($settings, 'data', []));
        $js = "$('#{$this->fieldConfig['options']['id']}').fileapi(" . Json::encode($settings) . ")";
        $view = Yii::$app->getView();
        FileApiAsset::register($view);
        $view->registerJs($js, $this->uploadPosition, $this->uploadKey);
    }
}