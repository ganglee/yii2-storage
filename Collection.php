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
        if (!($object->getAdapter() instanceof UploadInterface)) {
            throw new InvalidConfigException("The file system {$id}'s adapter should be implement by callmez\\storage\\UploadInterface.");
        }
        return $object;
    }
}