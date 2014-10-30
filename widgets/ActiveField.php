<?php
namespace callmez\storage\widgets;

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
                'class' => 'btn btn-default'
            ];
        }
        if ($options['buttonOptions'] !== false) {
            $value = isset($options['buttonOptions']['value']) ? $options['buttonOptions']['value'] : '选择';
            $button = Html::buttonInput($value, $options['buttonOptions']);
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
        unset($options['inputTemplate'], $options['buttonOptions'], $options['imgOptions']);

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