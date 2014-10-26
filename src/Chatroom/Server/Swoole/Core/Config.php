<?php

namespace Swoole\Core;

use Swoole\Common\Dir;

class Config
{

    private static $config;

    public static function load($configPath)
    {
        $files = Dir::tree($configPath, "/.php$/");
        $config = array();
        if (!empty($files)) {
            foreach ($files as $file) {
                $config += include "{$file}";
            }
        }
        self::$config = $config;
        return $config;
    }

    public static function loadFiles(array $files)
    {
        $config = array();
        foreach($files as $file) {
            $config += include "{$file}";
        }
        self::$config = $config;
        return $config;
    }

    public static function get($key, $default = null, $throw = false)
    {
        $result = isset(self::$config[$key]) ? self::$config[$key] : $default;
        if ($throw && empty($result)) {
            throw new \Exception("{key} config empty");
        }
        return $result;
    }

    public static function getField($key, $filed, $default = null, $throw = false)
    {
        $result = isset(self::$config[$key][$filed]) ? self::$config[$key][$filed] : $default;
        if ($throw && empty($result)) {
            throw new \Exception("{key} config empty");
        }
        return $result;
    }

    public static function all()
    {
        return self::$config;
    }
}
