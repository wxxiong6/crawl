<?php
namespace library;
use library\Out;
use library\Import;
use Exception;

class App
{
    public $config;
    public $argv;

    public function __construct($config){
        $this->config = $config;
        if(!isset($_SERVER['argv']))
            throw new \Exception("argv are empty", 0);
            array_shift($_SERVER['argv']);
            $this->argv = $_SERVER['argv'];
    }

    /**
     * 执行程序
     * @param object $import
     * @param array $argv
     */
    public  function run()
    {
        try {
            $import = new Import($this->config);
            $className = get_class($import);
            if( ! method_exists($import, $this->argv[0])){
                throw new Exception("{$className}->{$this->argv[0]} not existent in " . __FILE__ . ' ('.__LINE__.')');
            }

            $method = array_shift($this->argv);
            $argv = $this->argv;
            call_user_func_array([$import, $method], $argv);
         } catch (Exception $e) {
             Out::error($e->getMessage());
         }
    }
}

