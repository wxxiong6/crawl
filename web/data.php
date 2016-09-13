<?php

require  realpath(__DIR__.'/../').'/code/config/config.php';

$config = include ROOT_PATH . '/code/config/dbconfig.php';
$db = new \library\db\MysqlPDO($config);

if(empty($_GET['action'])){
    $_GET['action'] = 'data';
}
$data = [];

$order  =  isset($_GET['order']) ? $_GET['order'] : 'id';
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$limit  = isset($_GET['limit']) ? $_GET['limit'] : 10;

if($_GET['action'] === 'data'){
    $data['total'] = $db->count('data');
    $data['rows'] = $db->findAll('data','','','',$limit, $offset);
} else if($_GET['action'] === 'setting'){
    $data['total'] = $db->count('setting');
    $data['rows'] = $db->findAll('setting','','','id,site,url,project,create_time',$limit, $offset);
}
die(json_encode($data));