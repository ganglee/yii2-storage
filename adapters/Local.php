<?php
namespace callmez\storage\adapters;

use Yii;
use yii\imagine\Image;
use yii\helpers\FileHelper;
use yii\base\InvalidConfigException;
use callmez\file\system\adapters\Local as LocalAdapter;
use callmez\storage\FileProcessInterface;
use League\Flysystem\Config;

class Local extends LocalAdapter implements FileProcessInterface
{
    /**
     * 指定上传操作类
     * @var string
     */
    public $uploaderClass = 'callmez\storage\uploaders\Local';

    /**
     * 缩略图存放目录
     */
    public $thumbnailDir = 'thumbnails';

    /**
     * 创建图片缩略图并返回缩略图保存路径(大文件极耗内存,慎用)
     * @param $path
     * @param array $options
     * @return null|string
     */
    public function getThumbnail($path, Config $config)
    {
        $data = $this->getWidth($path);
        if (empty($data)) {
            return null;
        }
        $width = $config->get('width');
        $height = $config->get('height');
        if($width && !$height) {
            $height = round(($width / $data['width']) * $data['height']);
        } elseif (!$width && $height) {
            $width = round(($height / $data['height']) * $data['width']);
        }
        $thumbnailPath = $this->thumbnailDir . '/' . implode("_{$width}_{$height}.", explode('.', $path));
        $thumbnailLocation = $this->applyPathPrefix($thumbnailPath);
        if (!is_file($thumbnailLocation) || $config->get('force')) {
            $location = $this->applyPathPrefix($path);
            FileHelper::createDirectory(dirname($thumbnailLocation));
            Image::thumbnail($location, $width, $height)->save($thumbnailLocation);
        }
        return $thumbnailPath;
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