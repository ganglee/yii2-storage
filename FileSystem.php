<?php
namespace callmez\storage;

use League\Flysystem\Util;
use League\Flysystem\Filesystem as BaseFileSystem;
use yii\base\InvalidParamException;

class FileSystem extends BaseFileSystem implements FileProcessInterface
{
    /**
     * 获取缩略图片地址
     * @param $path
     * @param array $options
     * @return mixed
     * @throws \yii\base\InvalidParamException
     */
    public function getThumbnail($path, array $options)
    {
        if (!isset($options['width']) && !isset($options['height'])) {
            throw new InvalidParamException("Missing 'width' or 'height' option.");
        }
        return $this->getAdapter()->getThumbnail($path, $options);
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