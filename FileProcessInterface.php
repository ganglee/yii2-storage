<?php
namespace callmez\storage;

interface FileProcessInterface
{
    public function getThumbnail($path, $width, $height, $config = null);
}