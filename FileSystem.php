<?php
namespace callmez\storage;

use League\Flysystem\Filesystem as BaseFileSystem;
use yii\base\InvalidParamException;

class FileSystem extends BaseFileSystem implements FileProcessInterface
{
    public function getThumbnail($path, array $options)
    {
        if (!isset($options['width']) && !isset($options['height'])) {
            throw new InvalidParamException("Missing 'width' or 'height' option.");
        }
        return $this->getAdapter()->getThumbnail($path, $options);
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