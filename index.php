<?php
define('TART_TIME', microtime(true));
define('TART_MEM', memory_get_usage());
defined('ROOT_PATH') or define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('LIB_PATH', ROOT_PATH . 'code/library' . DIRECTORY_SEPARATOR);
defined('LOG_PATH') or define('LOG_PATH', ROOT_PATH . 'log' . DIRECTORY_SEPARATOR);
defined('TEP_PATH') or define('TEP_PATH', ROOT_PATH . 'tmp' . DIRECTORY_SEPARATOR);
defined('DATA_PATH') or define('DATA_PATH', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR);
// 环境常量
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

define('APP_DEBUG', true);

$config = include ROOT_PATH . '/code/config/dbconfig.php';
// 载入Loader类
require LIB_PATH . 'Loader.php';
\library\Loader::register();

$db = new \library\db\MysqlPDO($config);

$import = new \library\Import($db);

try {
    
    if (isset($argv['1']) && $argv[1] == 'install') {
        $import->install();
    } else 
        if (isset($argv['1']) && $argv[1] == 'run') {
            if (empty($argv['2'])) {
                exit("input siteid Please!");
            }
            $siteId = $argv['2'];
            $import->listWrite($siteId); // 下载列表
            $import->listRead($siteId); // 下载内容页面
            $import->detailRead($siteId); // 提取相关内容
        } else 
            if (isset($argv['1']) && $argv[1] == 'clear') {
                if (empty($argv['2'])) {
                    exit("input siteid Please!");
                }
                $import->clear($siteId);
            }
} catch (Exception $e) {
    echo $e->getMessage();
}


