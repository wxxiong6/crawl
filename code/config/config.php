<?php
define('TART_TIME', microtime(true));
define('TART_MEM', memory_get_usage());
defined('ROOT_PATH') or define('ROOT_PATH', dirname(dirname(__DIR__)). DIRECTORY_SEPARATOR);
define('LIB_PATH', ROOT_PATH . 'code/library' . DIRECTORY_SEPARATOR);
defined('LOG_PATH') or define('LOG_PATH', ROOT_PATH . 'log' . DIRECTORY_SEPARATOR);
defined('TEP_PATH') or define('TEP_PATH', ROOT_PATH . 'tmp' . DIRECTORY_SEPARATOR);
defined('DATA_PATH') or define('DATA_PATH', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR);
defined('DB_NAME') or define('DB_NAME', 'crawl');

// 环境常量
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

define('APP_DEBUG', false);

define('HELP_MSG', $helpMsg);

// 载入Loader类
require LIB_PATH . 'Loader.php';
\crawl\library\Loader::register();