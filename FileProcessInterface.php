<?php
namespace callmez\storage;

use League\Flysystem\Config;

interface FileProcessInterface
{
    public function setBaseUrl($url);
    public function getBaseUrl();
    public function getWidth($path);
    public function getHeight($path);
    public function getExif($path);
    public function thumbnail($path, Config $config);
}