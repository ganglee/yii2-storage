<?php
namespace callmez\storage;

use yii\base\InvalidParamException;
use League\Flysystem\Util;
use League\Flysystem\Filesystem as BaseFileSystem;

class FileSystem extends BaseFileSystem
{
    public function getBaseUrl()
    {
        return $this->getAdapter()->getBaseUrl();
    }

    public function setBaseUrl($url)
    {
        return $this->getAdapter()->setBaseUrl($url);
    }

    /**
     * 缩略图片并返回地址
     * @param $path
     * @param array $config 可选参数 width, height, absoluteUrl
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function thumbnail($path, array $config = [])
    {
        $config = $this->prepareConfig($config);
        if (!$config->get('width') && !$config->get('width')) {
            throw new InvalidParamException("The width and height is arranged with at least one");
        }
        $path = $this->getAdapter()->getThumbnail($path, $config);
        return $config->get('absoluteUrl', true) ? $this->getAbsoluteUrl($path) : $path;
    }

    /**
     * 获取文件的绝对Url
     * @param $path
     * @return string
     */
    public function getAbsoluteUrl($path)
    {
        return $this->getBaseUrl() . $path;
    }

    /**
     * 获取图片宽度
     * @param $path
     */
    public function getWidth($path)
    {
        return $this->getImageInfo($path, 'width');
    }

    /**
     * 获取图片高度
     * @param $path
     * @return bool
     */
    public function getHeight($path)
    {
        return $this->getImageInfo($path, 'height');
    }

    /**
     * 获取图片信息
     * @param $path
     * @param $key
     * @return bool
     */
    protected function getImageInfo($path, $key)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        $metaData = $this->getCache()->getMetadata($path);
        if (isset($metaData[$key])) {
            return $metaData[$key];
        } elseif (!($object = $this->getAdapter()->{'get' . $key}($path)) || !isset($object[$key])) {
            return false;
        }
        $this->getCache()->updateObject($path, $object, true);
        return $object[$key];
    }

    /**
     * `
     * @param $path
     */
    public function getExif($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);
        $metaData = $this->getCache()->getMetadata($path);
        if (isset($metaData['exif'])) {
            return $metaData['exif'];
        } elseif (! $object = $this->getAdapter()->getExif($path)) {
            return false;
        }
        $object = [
            'exif' => $object
        ];
        return $this->getCache()->updateObject($path, $object, true);
    }


//    /**
//     * Adapter 方法映射
//     * @param string $method
//     * @param array $arguments
//     * @return mixed
//     * @throws LogicException
//     */
//    public function __call($method, array $arguments)
//    {
//        if (method_exists($this->getAdapter(), $method)) {
//            return call_user_func_array([$this->getAdapter(), $method], $arguments);
//        }
//        return parent::__call($method, $arguments);
//    }
}