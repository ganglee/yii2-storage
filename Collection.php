<?php
namespace callmez\storage;

use Yii;
use yii\base\InvalidConfigException;
use callmez\file\system\Colletion as FileSystemCollection;

class Collection extends FileSystemCollection
{
    public function create($id, $config)
    {
        $object = parent::create($id, $config);
        if (!($object instanceof FileSystem)) {
            throw new InvalidConfigException("The file system class {$id} must extend from callmez\\storage\\FileSystem.");
        } elseif (!($object->getAdapter() instanceof UploadInterface)) {
            throw new InvalidConfigException("The file system adapter shuld be implement by callmez\\storage\\UploadInterface.");
        }
        return $object;
    }
}