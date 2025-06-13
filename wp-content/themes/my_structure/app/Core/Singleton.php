<?php

namespace Core;

class Singleton
{
    private static $instances = [];

    protected function __construct() {}

    public static function getInstance() {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }

        return self::$instances[$class];
    }

    private function __clone() {
        throw new \Exception("Cloning of this object is not allowed.");
    }
    public  function __wakeup() {
        throw new \Exception("Deserializing of this object is not allowed.");
    }
}