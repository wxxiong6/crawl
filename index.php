<?php

require __DIR__.'/code/config/config.php';

$config = include ROOT_PATH . '/code/config/dbconfig.php';
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
                $siteId = $argv['2'];
                $import->clear($siteId);
            }
} catch (Exception $e) {
    echo $e->getMessage();
}


