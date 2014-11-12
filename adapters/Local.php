<?php
namespace callmez\storage\adapters;

use Yii;
use yii\base\InvalidConfigException;
use callmez\file\system\adapters\Local as LocalAdapter;
use callmez\storage\FileProcessInterface;

class Local extends LocalAdapter implements FileProcessInterface
{
    public $uploaderClass = 'callmez\storage\uploaders\Local';

    public function getThumbnail($path, array $options)
    {
        if ($data = $this->getWidth($path)) {
            if(isset($options['width']) && !isset($options['height'])) {
                $options['height'] = $data['height'] * ($data['width'] / $options['width']);
            } elseif (!isset($options['width']) && isset($options['height'])) {
                $options['width'] = $data['width'] * ($data['height'] / $options['height']);
            }

        }
        return null;
    }

    /**
     * 获取图片宽度
     * @param $path
     * @return array
     */
    public function getWidth($path)
    {
        list($width, $height) = @getimagesize($this->applyPathPrefix($path));
        return $width ? compact('width', 'height') : null;
    }

    /**
     * 获取图片高度
     * @param $path
     * @return array
     */
    public function getHeight($path)
    {
        return $this->getWidth($path);
    }

    /**
     * 获取图片exif信息
     * @param $path
     * @return null|array
     */
    public function getExif($path)
    {
        $data = @exif_read_data($this->applyPathPrefix($path), null, true, false);
        return isset($data['EXIF']) ? $data['EXIF'] : null;
    }
}