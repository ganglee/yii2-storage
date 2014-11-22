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
    public $fieldConfig = [];
    public $fileInputOptions = [];
    public $previewOptions = [
        'class' => 'img-thumbnail'
    ];
    /**
     * 需要渲染的节点, 只能渲染{preview}, {fileInput}, 其他节点需要到ActiveField实例中渲染
     * @var array
     */
    public $parts = [];
    /**
     * 该设置会在未设置fileConfig['template']下渲染默认的ActiveField模板
     * @var array
     */
    public $fieldParts = [
        '{input}' => "<p class=\"input-group\">\n{input}\n<span class=\"input-group-btn\">\n<span class=\"btn btn-default file-input\">\n选择文件\n{fileInput}\n</span>\n</span>\n</p>\n{preview}",
    ];
    /**
     * @var string 存储上传类
     */
    public $uploader;

    public function init()
    {
        if (!($this->form instanceof ActiveForm)) {
            throw new InvalidConfigException("The 'form' property must instance of class " . ActiveForm::className() . ".");
        } elseif (!($this->model instanceof Model)) {
            throw new InvalidConfigException("The 'model' property must instance of class " . Model::className() . ".");
        } elseif (!($this->uploader instanceof AbstractUploader)) {
            throw new InvalidConfigException("The 'attribute' property must be set and instance of " . AbstractUploader::className() . ".");
        }
        // fileApi需要ID来指定作用域
        $this->fieldConfig['options'] = ArrayHelper::merge([
            'id' => Html::getInputId($this->model, $this->attribute) . '-wrapper',
            'class' => 'form-group'
        ], ArrayHelper::getValue($this->fieldConfig, 'options', []));
    }

    /**
     * @return object
     */
    public function run()
    {
        if (!isset($this->parts['{preview}'])) {
            $this->parts['{preview}'] = Html::img($this->getPreviewUrl(), $this->previewOptions);
        }
        if (!isset($this->parts['{fileInput}'])) {
            $this->parts['{fileInput}'] = Html::fileInput('file', null, $this->fileInputOptions);
        }
        if (isset($this->fieldConfig['template'])) { // 自定义模板则全解析元素
            $this->fieldConfig['template'] = strtr($this->fieldConfig['template'], $this->parts);
        }
        $field = $this->form->field($this->model, $this->attribute, $this->fieldConfig);
        if (!isset($this->fieldConfig['template'])) { // 默认模板和ActiveForm模板则模板替换主元素解析
            $field->template = strtr(strtr($field->template, $this->fieldParts), $this->parts);
        }
        $this->registerJs();
        return $field;
    }

    /**
     * 预览图的缩略设置
     * @var array
     */
    public $preview = [
        'width' => 100
    ];
    /**
     * 获取预览缩略图url
     * @return mixed
     */
    public function getPreviewUrl()
    {
        $value = Html::getAttributeValue($this->model, $this->attribute);
        return $value ? Yii::$app->storage->thumbnail($value, $this->preview) : false;
    }

    /**
     * registerJs 上传设置
     * @var array
     */
    public $upload = [
        'settings' => [
            'autoUpload' => true // 默认开启自动上传
        ],
    ];
    /**
     * JS上传设置, 以FileAPI上传插件为基础
     * @param $settings
     * @param int $position
     */
    public function registerJs()
    {
        $settings = $this->uploader->getUploadSettings(ArrayHelper::getValue($this->upload, 'settings', []));
        $settings['url'] = Url::to(ArrayHelper::getValue($settings, 'url', ''), true);
        $request = Yii::$app->getRequest();
        $settings['data'] = array_merge([ // csrf验证
            $request->csrfParam => $request->getCsrfToken(),
            'storage' => Yii::$app->storage->defaultFileSystem
        ], ArrayHelper::getValue($settings, 'data', []));
        $js = "$('#{$this->fieldConfig['options']['id']}').fileapi(" . Json::encode($settings) . ")";
        $view = Yii::$app->getView();
        FileApiAsset::register($view);
        $view->registerJs(
            $js,
            ArrayHelper::getValue($this->upload, 'position', View::POS_READY),
            ArrayHelper::getValue($this->upload, 'key')
        );
    }
}