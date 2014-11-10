<?php
namespace callmez\storage;

interface FileProcessInterface
{
    public function getThumbnail($path, array $options);
//    public function getWidth($path);
//    public function getHeight($height);
}