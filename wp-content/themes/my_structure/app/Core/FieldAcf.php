<?php

namespace Core;

class FieldAcf
{
    protected $key;
    protected $label;
    protected $name;
    protected $type = '';
    protected $value;

    public function __construct($key) {
        $this->key = $key;
        $this->label = $this->getLabelBySlug($key);
        $this->name = $key;
    }

    public function getKey() {
        return $this->key;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getValue() {
        return $this->value;
    }

    public function setKey($key) {
        $this->key = $key;
        return $this;
    }

    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    public function setName($name) {
        $this->name =$name;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setValue(&$value)
    {
        $this->value = $value;
        return $this;
    }

    public function getLabelBySlug($key)
    {
        $string = str_replace("_", " ", $key);
        $string = strtolower($string);
        $string = ucfirst($string);
        return $string;
    }
}