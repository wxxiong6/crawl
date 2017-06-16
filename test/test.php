<?php
require_once 'code/library/db/MysqlPDO.php';
// TODO Auto-generated MysqlPDOTest::setUp()
$dbconfig = array (
    'host' => 'mysql:host=localhost;dbname=test;',
    'user' => 'root',
    'password' => 'root',
    'tablePrefix' => 'pdo_',
);

$mysqlPDO = new \crawl\library\db\MysqlPDO($dbconfig);

// $mysqlPDO->exec('CREATE TABLE IF NOT EXISTS pdo_test(
//                  id INT NOT NULL AUTO_INCREMENT,
//                  name VARCHAR(100) NOT NULL,
//                  email VARCHAR(100) NOT NULL,
//                  PRIMARY KEY (id));');


 $mysqlPDO->insertAll('pdo_test',[
     [
     'name' => "xwx123'33",
     'email' => 'xwx@gmail.com'
    ],
     [
     'name' => "333333",
     'email' => 'x333x@gmail.com'
         ],
 ]);

 $mysqlPDO->insertAll('pdo_test',[
     [
         'name' => "xwx123'33ddddddddddddddddddddd",
         'email' => 'xwdddddddddddddddddddd@gmail.com'
     ],
 ]);
$mysqlPDO->delete('pdo_test', ['id' => '167']);
$mysqlPDO->find('pdo_test',['id'=>'1']);
$set = $mysqlPDO->update('pdo_test',['id'=>'1'],['name'=>'33333333333333','email'=>'2@qq.com']);
var_dump($set);
print_r($mysqlPDO->getSqlList());