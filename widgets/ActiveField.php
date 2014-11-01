<?php
namespace callmez\storage\widgets;

use callmez\storage\assets\FileApiAsset;
use Yii;
use yii\helpers\Html;
use callmez\storage\helpers\StorageHelper;

class ActiveField extends \yii\widgets\ActiveField
{
    public function uploadInput($options = [])
    {
        $button = '';
        if (!isset($options['buttonOptions'])) {
            $options['buttonOptions'] = [
                'class' => 'file-input btn btn-default'
            ];
        }
        if ($options['buttonOptions'] !== false) {
            $value = isset($options['buttonOptions']['value']) ? $options['buttonOptions']['value'] : '选择';
            $value .= Html::fileInput('file', null, isset($options['multiple']) ? [
                'mutiple' => $options['multiple']
            ]: []);
            $button = Html::button($value, $options['buttonOptions']);
            FileApiAsset::register(Yii::$app->getView());
        }

        $img = '';
        if (!isset($options['imgOptions'])) {
            $options['imgOptions'] = [
                'width' => 40,
                'height' => 40
            ];
        }
        if ($options['imgOptions'] !== false) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
            $img = Html::img(StorageHelper::getAssetUrl($value), $options['imgOptions']);
        }

        $inputTemplate = isset($options['inputTemplate']) ? $options['inputTemplate'] :
            '{img}<div class="input-group">{input}<span class="input-group-btn">{button}</span></div>';
        unset($options['inputTemplate'], $options['multiple'], $options['buttonOptions'], $options['imgOptions']);

        $options = array_merge($this->inputOptions, $options);
        $this->adjustLabelFor($options);
        $input = Html::activeTextInput($this->model, $this->attribute, $options);

        $this->parts['{input}'] = strtr($inputTemplate, [
            '{input}' => $input,
            '{button}' => $button,
            '{img}' => $img
        ]);

        return $this;
    }
}