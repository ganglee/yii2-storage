<?php
namespace callmez\storage;

use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\base\Component;
use yii\base\InvalidConfigException;
use callmez\storage\assets\FileApiAsset;
use callmez\storage\models\Storage;

abstract class BaseStorage extends Component
{
    /**
     * 上传的存储容器名
     * @var string
     */
    public $name;
    /**
     * 存储目录
     * @var string
     */
    public $baseUrl;
    /**
     * 上传文件默认保存的目录
     * @var string
     */
    public $basePath;
    /**
     * 图片存储数据库AR类
     * @var string
     */
    public $modelClass = 'app\models\Storage';

    /**
     * 上传文件域
     * @var string
     */
    public $filesFieldName = 'file';

    public function init()
    {
        if ($this->name === null) {
            throw new InvalidConfigException('Storage::name must be set. ');
        }
    }

    /**
     * 判断是否有上传
     * @return mixed
     */
    abstract public function isUploaded();

    /**
     * 保存上传文件
     * @param callable $saveCallback
     * @return mixed
     */
    abstract public function saveUploaded(\Closure $saveCallback = null);

    abstract public function getPath($path);

    abstract public function getUrl($path);

    abstract public function getThumbnail($path, $url = true, array $options = []);

    abstract public function getWidth($path);

    abstract public function getHeight($path);

    abstract public function getExif($path);

    /**
     * 上传图片的js代码控制, 以FileAPI插件为基础
     * @see https://github.com/RubaXa/jquery.fileapi
     * @param array $options
     * @return bool
     */
    public function registerUploadJs(array $options = [])
    {
        $view = Yii::$app->controller->getView();
        if (isset($options['uploadSettings']) && is_string($options['uploadSettings'])) { // 如果是字符串的话则合成js
            $js = Json::encode($this->createUploadSetting());
            $js = "$.extend({$js}, {$options['uploadSettings']})";
        } else {
            $js = Json::encode($this->createUploadSetting(isset($options['uploadSettings']) ? $options['uploadSettings'] : []));
        }
        !isset($options ['button']) && $options ['button'] = '[data-toggle="upload"]';
        if ($options['button'] === false) { //不注册代码直接返回js设置, 用于定制代码
            return $js;
        }
        $fileApi = FileApiAsset::register($view);
        isset($options['fileApiSettings']) && $fileApi->settings = $options['fileApiSettings'];

        if (!isset($options ['position'])) { //js代码位置 @see View::registerJs()
            $options['position'] = View::POS_READY;
        }
        if (!isset($options['key'])) { //js命名  @see View::registerJs()
            $options['key'] = null;
        }
        if (isset($options['js'])) {
            $options['js'] = is_array($options['js']) ? ';' . Json::encode($options['js']) : $options['js'];
        } else {
            $options['js'] = ';';
        }
        $js = "$('{$options ['button']}').fileapi({$js}){$options['js']}";
        $view->registerJs($js, $options['position'], $options['key']);
        return true;
    }

    /**
     * 上传基本设置
     * @param array $settings
     * @throws InvalidConfigException
     * @return Ambigous <\yii\web\JsExpression, string>
     */
    public function createUploadSetting(array $settings = [])
    {
        $settings['url'] = Url::to(isset($settings['url']) ? $settings['url'] : '');
        if (!isset($settings['data'])) {
            $request = Yii::$app->getRequest();
            $settings['data'] = array(
                $request->csrfParam => $request->getCsrfToken()
            );
        }
        return $settings;
    }

    /**
     * 保存图片信息到数据库
     * @param \app\models\Storage $model
     * @param array $data
     * @return bool
     */
    protected function saveModel(Storage $model, array $data)
    {
        $model->setAttributes(array_merge($data, [
            'bin' => $this->name
        ]));
        return $model->save();
    }
}