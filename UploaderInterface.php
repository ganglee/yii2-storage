<?php
namespace callmez\storage;


interface UploaderInterface
{
    public function __construct($fieldName);
    public function isUploaded();
    public function validate(callable $callback = null);
    public function save($target);
    public function getSize();
    public function getName();
    public function getType();
    public function hasError();
    public function getError();
    public function setError($error);
}