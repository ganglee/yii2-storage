<?php
namespace callmez\storage\bin;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use callmez\storage\Storage;

/**
 *
 * Yii2中默认开启了csrf 验证. 所以在回调验证时.必须先关闭csrf验证, 否则Yii2会丢弃请求
 *
 * 该类以callback方式实现上传功能不支持return跳转形式
 *
 * 七牛上传类***上传类以callback上传方式为基础. 省事省力省带宽***
 * @author CallMeZ
 *
 */
class QiniuStorage extends BaseStorage
{
    /**
     * 七牛的url默认留空
     * @var string
     */
    public $baseUrl;
    /**
     * 七牛的路径为根目录
     * @var string
     */
    public $basePath = '';

    /**
     * @var string 上传的存放空间名
     */
    public $bucket;
    /**
     * @var string 上传的键值key
     */
    public $accessKey;
    /**
     * @var string 上传的秘钥key
     */
    public $secretKey;
    /**
     * 七牛上传地址
     * @var unknown
     */
    public $qiniuUploadUrl;
    /**
     * 七牛默认配置参数.主要用户token验证和属性获取
     * 默认以 callback 方式回调上传
     * @var array
     */
    public $qiniuUploadConfig = [
        'SaveKey' => '$(year)/$(etag)', // 设定图片保存格式
        'MimeLimit' => 'image/*', // 默认只接受图片上传
        'CallbackUrl' => '', // '' 为当前URL地址
        'CallbackBody' => [
            'path' => '$(key)',
            'name' => '$(fname)',
            'size' => '$(fsize)',
            'mimeType' => '$(mimeType)',
            'exif' => '$(exif)',
            'width' => '$(imageInfo.width)',
            'height' => '$(imageInfo.height)',
        ]
    ];

    public function init()
    {
        parent::init();

        if ($this->bucket === null) {
            throw new InvalidConfigException('"bucket" property must be set.');
        } elseif ($this->accessKey === null) {
            throw new InvalidConfigException('"accessKey" property must be set.');
        } elseif ($this->secretKey === null) {
            throw new InvalidConfigException('"secretKey" property must be set.');
        }

        Qiniu_SetKeys($this->accessKey, $this->secretKey);

        if ($this->qiniuUploadUrl === null) { //使用默认上传地址
            global $QINIU_UP_HOST;
            $this->qiniuUploadUrl = $QINIU_UP_HOST;
        }
        if ($this->baseUrl === null) {
            $this->baseUrl = strtr('http://{bucket}.qiniudn.com', ['{bucket}' => $this->bucket]);
        }
    }

    /**
     * 判断 是否有七牛的回调上传请求
     * Apache下的.htaccess配置
     * #七牛云存储的回调会传送HTTP_AUTHORIZATION认证秘钥,一定要放在最rewrite的后面.防止影响
     * #PHP在CGI模式下的认证信息的获取
     * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
     * @return bool|nullsaveUploaded
     */
    public function isUploaded()
    {
        return !empty($_SERVER['HTTP_AUTHORIZATION']);
    }

    /**
     * 验证上传
     *
     * @see \app\components\BaseStorage::checkUpload()
     */
    public function checkUploaded()
    {
        return explode(' ', $_SERVER['HTTP_AUTHORIZATION'])[1] === $this->createCallbackToken();
    }

    /**
     * 保存上传的图片, 因为七牛只能单图片回调上传
     * @param callable $saveCallback
     * @return array|bool
     */
    public function saveUploaded(\Closure $saveCallback = null)
    {
        $user = Yii::$app->getUser();
        if (!$user->getIsGuest() && $this->checkUploaded() && isset($_POST['path'])) { // 必须回调path地址
            $path = $_POST['path'];
            $this->setFile($path, $_POST); // 缓存上传后文件属性

            $return = [];

            $model = new $this->modelClass;
            $this->saveModel($model, [
                'name' => pathinfo($this->getFile($path, 'name'))['filename'],
                'uid' => $user->getId(),
                'path' => $path,
                'size' => $this->getFile($path, 'size'),
                'mime_type' => $this->getFile($path, 'mimeType'),
            ]);
            if (($saveCallback !== null) && ($result = $saveCallback($model)) !== null) {
                $return[] = $result;
            }

            return $return !== [] ? $return : true;
        }
        return false;
    }

    public function getPath($path)
    {
        return $path;
    }

    public function getUrl($path)
    {
        return $this->baseUrl . $path;
    }

    /**
     * 缩略图选项
     * @var array
     */
    private $_thumbnailOptions = [
        'width' => 'w',
        'height' => 'h',
        'quality' => 'q'
    ];

    /**
     * 缩略图
     * @param $path
     * @param array $options
     * @return string
     */
    public function getThumbnail($path, $url = true, array $options = [])
    {
        $params = [
            'imagesView2' => array_key_exists('imageView2', $options) ? $options['imageView2'] : 1 // 默认用imageView2的模式来缩略图片
        ];
        foreach ($this->_thumbnailOptions as $k => $v) {
            if (array_key_exists($k, $options)) {
                $params[$k] = $options[$k];
            }
        }
        $path = $path . (strpos($path, '?') === false ? '?' : '&') . http_build_query($params);
        return isset($options['url']) && $options['url'] ? $this->getUrl($path) : $path;
    }

    public function getWidth($path)
    {
        return $this->getFile($path, 'width');
    }

    public function getHeight($path)
    {
        return $this->getFile($path, 'height');
    }

    public function getExif($path)
    {
        return $this->getFile($path, 'exif');
    }

    /**
     * 文件缓存
     * @var
     */
    public $_filesCache;

    /**
     * 获取存储的文件及其属性, 或从七牛服务器获取文件属性后缓存来节省开销
     * @param $path
     * @param $propertyName
     * @return null
     */
    public function getFile($path, $propertyName)
    {
        if (!array_key_exists($path, $this->_filesCache)) {
            $load = true;
        }
        if (!array_key_exists($propertyName, $this->_filesCache[$path]) && $load !== true) {
            $load = true;
        }

        if (isset($this->_filesCache[$path][$propertyName])) {
            return $this->_filesCache[$path][$propertyName];
        }
        return null;
    }

    /**
     * 记录存储文件到缓存中
     * @param $path
     * @param $value
     * @param null $key
     */
    public function setFile($path, $value, $key = null)
    {
        if ($key === null) {
            $this->_filesCache[$path] = $value;
        } else {
            $this->_filesCache[$path][$key] = $value;
        }
    }

    /**
     * 七牛的回调token生成
     * 用来验证安全和回调参数的真实性
     *
     * @param array|string $callbackUrl
     * @param array|string $callbackBody
     * @return string
     */
    public function createCallbackToken($callbackUrl = '', array $callbackBody = [])
    {
        $mac = Qiniu_RequireMac(null);

        if ($callbackBody === [] && is_array($_POST)) { // 只接受 post的上传数据
            $callbackBody = $this->httpBuildQuery($_POST, false); // 必须转换成标准的url query格式以便生成token
        }

        $request = new \Qiniu_Request([
            'path' => Url::to($callbackUrl, true)
        ], $callbackBody);
        return $mac->SignRequest($request, true);
    }

    /**
     * 七牛的jqueryFileUpload组件上传基本设置
     * @param array $settings
     * @throws InvalidConfigException
     * @return Ambigous <\yii\web\JsExpression, string>
     */
    public function createUploadSetting(array $settings = [])
    {
        $settings = parent::createUploadSetting($settings);
        if (!isset($settings['formData']['token'])) {
            $settings['formData']['token'] = $this->createUploadToken([
                'CallbackUrl' => $settings['url'], // 根据url设置上传token
            ]);
        }
        $settings['url'] = $this->qiniuUploadUrl; //上传地址使用qiniu默认的上传地址
        return $settings;
    }

    /**
     * 创建七牛上传token
     * @param array $params
     * @return string
     */
    public function createUploadToken(array $params = [])
    {
        $putPolicy = new \Qiniu_RS_PutPolicy($this->bucket);

        $params = $params === [] ? $this->qiniuUploadConfig : ArrayHelper::merge($this->qiniuUploadConfig, $params);
        if (!isset($params['CallbackUrl']) || empty($params['CallbackBody'])) {
            throw new InvalidConfigException('callback parameters missing.');
        }
        foreach ($params as $key => $value) {
            if (property_exists($putPolicy, $key)) {
                if ($key == 'CallbackUrl') { // 上传地址生成
                    $value = Url::to($value, true);
                } elseif ($key == 'CallbackBody' && is_array($value)) { // 上传参数组成
                    $value = $this->httpBuildQuery($value);
                }
                $putPolicy->$key = $value;
            }
        }
        return $putPolicy->Token(null);
    }

    /**
     * 创建url参数
     * http_build_query 会修改 字符的编码.
     * 默认开启字符编码转义
     *
     * @param array $data
     * @param boolean $variableTag
     * @return string
     */
    protected function httpBuildQuery(array $data, $variableTag = true)
    {
        $string = http_build_query($data);
        return $variableTag ? strtr($string, [
            '%24' => '$',
            '%28' => '(',
            '%29' => ')'
        ]) : $string;
    }
}