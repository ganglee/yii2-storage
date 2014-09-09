<?php
namespace yii\storage\bin;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\storage\Storage;

class LocalStorage extends Storage
{
    /**
     * 文件保存目录url
     * @var string
     */
    public $baseUrl = '@web/storage';
    /**
     * 文件保存目录路径
     * @var string
     */
    public $basePath = '@webroot/storage';

    /**
     * 是否有上传文件
     * @param string $name
     * @return bool
     */
    public function isUploaded()
    {
        return $this->filesFieldName ? isset($_FILES[$this->filesFieldName]) : !empty($_FILES);
    }

    /**
     * 保存上传文件, 支持批量上传
     * @param callable $saveCallback 文件保存后的回调
     * @param string $name $_FILES的field名
     * @return array|bool
     */
    public function saveUploaded(\Closure $saveCallback = null)
    {
        $files = UploadedFile::getInstancesByName($this->filesFieldName);
        $user = Yii::$app->getUser();
        if (!$user->getIsGuest() && !empty($files)) {
            $model = new $this->modelClass;

            $return = [];
            foreach ($files as $k => $file) {
                $filePath = $this->hashPath($file->name);
                $path = $this->getPath($filePath);
                FileHelper::createDirectory(dirname($path));
                if ($file->saveAs($path)) {
                    $_model = clone $model;
                    $this->saveModel($_model, [
                        'name' => pathinfo($file->name)['filename'],
                        'uid' => $user->getId(),
                        'path' => $filePath,
                        'size' => $file->size,
                        'mime_type' => $file->type,
                    ]);
                    if (($saveCallback !== null) && ($result = $saveCallback($_model)) !== null) {
                        $return[] = $result;
                    }
                }
            }
            return $return !== [] ? $return : true;
        }
        return false;
    }

    public function getPath($path)
    {
        return Yii::getAlias($this->basePath) . $path;
    }

    public function getUrl($path)
    {
        return Yii::getAlias($this->baseUrl) . $path;
    }

    public function getThumbnail($path, $url = true, array $options = [])
    {
        return $url ? $this->getUrl($path) : $path;
    }

    public function getWidth($path)
    {
        return getimagesize($this->getPath($path))[0];
    }

    public function getHeight($path)
    {
        return getimagesize($this->getPath($path))[1];
    }

    public function getExif($path)
    {
        return null;
    }

    /**
     * 文件名hash后的路径
     * @param null $name
     * @return string
     */
    public function hashPath($name = null, $time = false)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $hash = md5(YII_BEGIN_TIME . Yii::$app->get('security')->generateRandomKey(8) . $name);
        $name = $hash . '.' . ($ext ? : 'attach');
        return '/' . ($time ? date('Y/m/d') : substr($hash, 0, 2)) . '/' . $name;
    }
}