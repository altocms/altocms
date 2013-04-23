<?php

class LangArray extends LsObject implements ArrayAccess {
    protected $_container = array();

    public function __construct($array = null) {
    }

    public function offsetExists($offset) {
    }

    public function offsetGet($offset) {
        return Engine::getInstance()->Lang_Get($offset);
    }

    public function offsetSet($offset, $value) {
    }

    public function offsetUnset($offset) {
    }
}

// EOF