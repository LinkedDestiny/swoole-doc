<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 */


namespace db;

class Redis
{
    private static $instances;
    private static $configs;

    public static function getInstance($config)
    {
        $name = $config['name'];
        $pconnect = $config['pconnect'];
        if (empty(self::$instances[$name])) {
            $redis = new \Redis();
            if ($pconnect) {
                $redis->pconnect($config['host'], $config['port'], $config['timeout'], $name);
            } else {
                $redis->connect($config['host'], $config['port'], $config['timeout']);
            }
            self::$instances[$name] = $redis;
            self::$configs[$name] = $config;
        }
        return self::$instances[$name];
    }

    /**
     * 手动关闭链接
     * @param array $names
     * @return bool
     */
    public static function closeInstance(array $names = array())
    {
        if (empty(self::$instances)) {
            return true;
        }

        if (empty($names)) {
            foreach (self::$instances as $name => $redis) {
                if (self::$configs[$name]['pconnect']) {
                    continue;
                }
                $redis->close();
                unset(self::$configs[$name]);
            }
        } else {
            foreach ($names as $name) {
                if (isset(self::$instances[$name])) {
                    if (self::$configs[$name]['pconnect']) {
                        continue;
                    }
                    self::$instances[$name]->close();
                    unset(self::$configs[$name]);
                }
            }
        }

        return true;
    }
}
