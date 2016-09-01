<?php
namespace library;

class Loader
{
    public static function autoload($class){
        $file = realpath(ROOT_PATH.'code/'.str_replace('\\','/', $class).'.php');
        if(!$file){
            throw new \Exception($class.'class is exists');
            return false;
        }
        include $file;
    }

    // 注册自动加载机制
    public static function register($autoload = '')
    {
        // 注册系统自动加载
        spl_autoload_register($autoload ?: 'library\\Loader::autoload', true, true);
    }
}

