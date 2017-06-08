<?php
namespace crawl\library;

class Loader
{

    public static function autoload($class)
    {
        $file = realpath(LIB_PATH . str_replace(__NAMESPACE__, '', $class) . '.php');
        if (! $file) {
            throw new \Exception($class . ' class is not exists');
        }
        include $file;
    }

    // 注册自动加载机制
    public static function register($autoload = '')
    {
        // 注册系统自动加载
        spl_autoload_register($autoload ?: '\\'.__NAMESPACE__.'\\Loader::autoload', true, true);
    }
}

