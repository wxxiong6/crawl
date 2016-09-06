<?php

defined('ROOT_PATH') or define('ROOT_PATH',dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('LIB_PATH', ROOT_PATH . 'code/library' . DIRECTORY_SEPARATOR);

$config =  include ROOT_PATH.'code/config/dbconfig.php';

// 载入Loader类
require LIB_PATH . 'Loader.php';
\library\Loader::register();

$db = new \library\db\MysqlPDO($config);

// 'order=asc&offset=0&limit=10'

$order  =  isset($_GET['order']) ? $_GET['order'] : '';
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$limit  = isset($_GET['limit']) ? $_GET['limit'] : 10;

$data['total'] = $db->count('data');
$data['rows'] = $db->findAll('data','','','',$limit, $offset);
die(json_encode($data));