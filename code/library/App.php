<?php
namespace library;
use library\Out;
class App
{
    /**
     * 执行程序
     * @param object $import
     * @param array $argv
     */
    public static function run($import, array $argv = [])
    {
        if( ! method_exists($import, $argv[1])){
             Out::error('error');
        }
        if (!isset($argv[2])) $argv[2] = '';
        call_user_func(array($import, $argv[1]) , $argv[2]);
    }
}

