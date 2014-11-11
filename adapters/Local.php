<?php
namespace callmez\storage\adapters;

use Yii;
use yii\imagine\Image;
use callmez\file\system\adapters\Local as LocalAdapter;
use callmez\storage\FileProcessInterface;

class Local extends LocalAdapter implements FileProcessInterface
{
    public $uploaderClass = 'callmez\storage\uploaders\Local';

    public function getThumbnail($path, array $options)
    {
        $location = $this->applyPathPrefix($path);
    }
    public function getWidth($path)
    {}
    public function getHeight($path)
    {}
    public function getExif($path)
    {
        $resource = Image::getImagine()->open($this->applyPathPrefix($path));

        return [
            ''
        ]
    }
}