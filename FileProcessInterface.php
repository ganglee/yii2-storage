<?php
namespace callmez\storage;

use League\Flysystem\Config;

interface FileProcessInterface
{
    public function getThumbnail($path, Config $config);
    public function getWidth($path);
    public function getHeight($path);
    public function getExif($path);
}