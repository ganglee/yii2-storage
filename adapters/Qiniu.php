<?php
namespace callmez\storage\adapters;

use Yii;
use callmez\file\system\adapters\Qiniu as QiniuAdapter;
use callmez\storage\FileProcessInterface;
use yii\helpers\ArrayHelper;
use League\Flysystem\Config;

//文件操作
require_once Yii::getAlias("@vendor/qiniu/php-sdk/qiniu/fop.php");

/**
 * 七牛文件存储类, 增加图片文件操作功能
 * @package callmez\storage\adapters
 */
class Qiniu extends QiniuAdapter implements FileProcessInterface
{
    /**
     * 文件上传类
     * @var string
     */
    public $uploaderClass = 'callmez\storage\uploaders\Qiniu';

    private $_baseUrl;

    /**
     * 获取基本Url, 默认为七牛bucket域名
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $this->setBaseUrl('http://' . $this->domain);
        }
        return $this->_baseUrl;
    }

    /**
     * 设置基本url
     * @param $url
     */
    public function setBaseUrl($url)
    {
        $this->_baseUrl = $url;
    }

    /**
     * 生成图片缩略图路径
     * @param $path
     * @param array $options
     * @return string
     */
    public function getThumbnail($path, Config $config)
    {
        $width = $config->get('width');
        $height = $config->get('height');
        $params = ['?imageView/2'];
        $width && $params[] = 'w/' . $width;
        $height && $params[] = 'h/' . $height;
        return '/' . $path . implode('/', $params);
    }

    /**
     * 获取图片宽
     * @param $path
     * @return float|int|mixed|\Services_JSON_Error|string|void
     */
    public function getWidth($path)
    {
        return $this->getImageInfo($path);
    }

    /**
     * 获取图片高
     * @param $path
     * @return float|int|mixed|\Services_JSON_Error|string|void
     */
    public function getHeight($path)
    {
        return $this->getImageInfo($path);
    }

    /**
     * 设为false则显示七牛返回的详细exif信息
     * @var bool
     */
    public $simpleExif = true;
    /**
     * 获取图片文件exif信息
     * @param $path
     * @return float|int|mixed|\Services_JSON_Error|string|void
     */
    public function getExif($path)
    {
        $data = json_decode(file_get_contents($this->getImageExifUrl($path)), true);
        if ($this->simpleExif) {
            $data = ArrayHelper::getColumn($data, 'val');
        }
        return is_array($data) ? ['exif' => $data, 'path' => $path] : null;
    }

    /**
     * 获取图片文件信息
     * @param $path
     * @return array|null
     */
    public function getImageInfo($path)
    {
        $data = json_decode(file_get_contents($this->getImageInfoUrl($path)), true);
        return is_array($data) ? $data + ['path' => $path] : null;
    }

    /**
     * 图片文件信息地址(可以获取私有文件)
     * @param $path
     * @return string
     */
    public function getImageInfoUrl($path)
    {
        $getPolicy = new \Qiniu_RS_GetPolicy();
        return $getPolicy->MakeRequest((new \Qiniu_ImageInfo)->MakeRequest($this->getUrl($path)), null);
    }
    /**
     * 图片exif信息地址(可以获取私有文件)
     * @param $path
     * @return string
     */
    public function getImageExifUrl($path)
    {
        $getPolicy = new \Qiniu_RS_GetPolicy();
        return $getPolicy->MakeRequest((new \Qiniu_Exif)->MakeRequest($this->getUrl($path)), null);
    }
}