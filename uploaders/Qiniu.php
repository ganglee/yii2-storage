<?php
namespace callmez\storage\uploaders;

use yii\web\UploadedFile;
use yii\base\InvalidParamException;

class Local extends AbstractUploader
{
    /**
     * 上传方式, 默认auto(自动判断)
     * @var string
     */
    private $_uploadType = 'auto';
    private $_uploadTypes = [
        'auto' => '自动判断',  // 自动判断 (默认)
        'remote' => '远程上传', // 图片直接上传到七牛服务器会回调参数, 能节省流量和服务器资源(推荐)
        'local' => '本地上传' // 图片上传到本地服务器, 可自行操作文件
    ];

    public function init()
    {
        if ($this->_uploadType == 'auto') { // 如果自动判断,则判断上传方式
            $this->setUploadType($this->isUploadedRemote() ? 'remote' : 'local');
        }
    }

    public function setUploadType($type)
    {
        if (!array_key_exists($type, $this->_uploadTypes)) {
            throw new InvalidParamException("The 'uploadType' value must be one of (" . implode(',', array_keys($this->_uploadTypes)) . ")");
        }
        $this->_uploadType = $type;
    }

    public function getUploadType()
    {
        return $this->_uploadType;
    }

    /**
     * 判断 是否有七牛的上传请求
     * Apache下的.htaccess配置
     * #七牛云存储的回调会传送HTTP_AUTHORIZATION认证秘钥,一定要放在最rewrite的后面.防止影响
     * #PHP在CGI模式下的认证信息的获取
     * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
     * @return bool|nullsaveUploaded
     */
    public function isUploaded()
    {
        return $this->{'isUploaded' . $this->_uploadType}();
    }

    /**
     * 判断是否上传到七牛服务器
     * @return bool
     */
    protected function isUploadedRemote()
    {
        return !empty($_SERVER['HTTP_AUTHORIZATION']);
    }

    /**
     * 判断是否上传到本地
     * @return UploadedFile
     */
    protected function isUploadedLocal()
    {
        return ($this->file = UploadedFile::getInstanceByName($this->fileKey) !== null);
    }

    /**
     * 验证上传
     *
     * @see \app\components\BaseStorage::checkUpload()
     */
    public function validate(callable $callback = null)
    {
        if (!$this->{'validate' . $this->_uploadType}()) {
            return false;
        } elseif ($callback !== null && !call_user_func($callback)) {
            return false;
        }
        return true;
    }

    /**
     * 验证远程上传
     * @return bool
     */
    protected function validateRemote()
    {
        if (explode(' ', $_SERVER['HTTP_AUTHORIZATION'])[1] === $this->createCallbackToken()) {
            return true;
        }
        $this->setError('Token 验证失败');
        return false;
    }

    /**
     * 验证本地上传
     * @return bool
     */
    protected function validateLocal()
    {
        if ($this->file === null) {
            $this->setError('未找到上传组件');
        } elseif ($this->file->getHasError()) {
            $error = isset(Qiniu::$errorText[$this->file->error]) ? Qiniu::$errorText[$this->file->error] : '未知错误';
            $this->setError($error);
        } else {
            return true;
        }
        return false;
    }

    /**
     * 保存上传的文件
     * @param $target
     * @return mixed
     */
    public function save($target)
    {
        return $this->{'save' . $this->_uploadType}($target);
    }

    /**
     * 七牛远程上传回调参数
     * @param $target
     */
    protected function saveRemote($target)
    {
        return true;
    }

    /**
     * 本地上传保存到七牛服务器
     * @param $target
     * @return mixed
     */
    protected function saveLocal($target)
    {
        return $this->fileSystem->updateStream($target, fopen($this->file->tempName, 'r+'));
    }

    public function getName()
    {}

    public function getSize()
    {}

    public function getType()
    {}

    /**
     * 七牛的回调token生成
     * 用来验证安全和回调参数的真实性
     *
     * @param array|string $callbackUrl
     * @param array|string $callbackBody
     * @return string
     */
    protected function createCallbackToken($callbackUrl = '', array $callbackBody = [])
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