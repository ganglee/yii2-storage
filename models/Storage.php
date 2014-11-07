<?php
namespace callmez\storage\models;

use yii\db\ActiveRecord;

class Storage extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%storage}}';
    }

    public function rules()
    {
        return [
            
        ];
    }
}