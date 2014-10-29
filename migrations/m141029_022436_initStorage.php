<?php

use yii\db\Schema;
use yii\db\Migration;
use callmez\storage\Storage;

class m141029_022436_initStorage extends Migration
{
    public function up()
    {
        $tableName = Storage::tableName();
        $this->createTable($tableName, [
            'id' => Schema::TYPE_PK,
            'uid' => Schema::TYPE_INTEGER . " UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传id'",
            'name' => Schema::TYPE_STRING . " NOT NULL DEFAULT '' COMMENT '原始文件名'",
            'path' => Schema::TYPE_STRING . " NOT NULL DEFAULT '' COMMENT '保存路径'",
            'size' => Schema::TYPE_INTEGER . " UNSIGNED NOT NULL DEFAULT '0' COMMENT '文件大小'",
            'mime_type' => Schema::TYPE_STRING . "(20) NOT NULL DEFAULT '' COMMENT '文件类型'",
            'bin' => Schema::TYPE_STRING . "(40) NOT NULL DEFAULT '' COMMENT '存储容器'",
            'category' => Schema::TYPE_STRING . " NOT NULL DEFAULT '' COMMENT '所属分类'",
            'status' => Schema::TYPE_BOOLEAN . " NOT NULL DEFAULT '0' COMMENT '附件存储状态'",
            'created_at' => Schema::TYPE_INTEGER . " UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间'",
            'updated_at' => Schema::TYPE_INTEGER . " UNSIGNED NOT NULL DEFAULT '0' COMMENT '修改时间'",
        ]);
        $this->createIndex('uid', $tableName, 'uid');
        $this->createIndex('category', $tableName, 'category');
    }

    public function down()
    {
        $this->dropTable(Storage::tableName());
        return true;
    }
}
