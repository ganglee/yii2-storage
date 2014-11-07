<?php
namespace callmez\storage\uploaders;

use yii\web\UploadedFile;

class Local extends AbstractUploader
{
    public $file;
    public $errorText = [
        UPLOAD_ERR_INI_SIZE => '文件超过超过了upload_max_filesize选项限制的值',
        UPLOAD_ERR_FORM_SIZE => '文件超过HTML表单中post_max_size选项指定的值',
        UPLOAD_ERR_PARTIAL => '文件上传未完成',
        UPLOAD_ERR_NO_FILE => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '上传文件未找到',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败',
        UPLOAD_ERR_EXTENSION => 'PHP上传功能没有启用'
    ];
    public function init()
    {
        $this->file = UploadedFile::getInstanceByName($this->fileName);
    }
    public function isUploaded()
    {
        return $this->file !== null;
    }

    public function validate(callable $callback = null)
    {
        if ($this->file === null) {
            return $this->setError('未找到上传组件');
        } elseif ($this->file->getHasError()) {
            $error = isset($this->errorText[$this->file->error]) ? $this->errorText[$this->file->error] : '未知错误';
            $this->setError($error);
        } elseif ($callback !== null && !call_user_func($callback)) {
        } else {
            return true;
        }
        return false;
    }

    public function save($target)
    {
        if ($this->hasError()) {
            return false;
        }
        return $this->fileSystem->updateStream($target, fopen($this->file->tempName, 'r+'));
    }
    public function getSize()
    {
        return $this->file->size;
    }
    public function getName()
    {
        return $this->file->name;
    }
    public function getType()
    {
        return $this->file->type;
    }
}