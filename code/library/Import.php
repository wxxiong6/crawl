<?php
namespace library;
use library\Out;
use Exception;
use library\Crawl;
use library\db\MysqlPDO;
class Import
{

    public $db;

    public function __construct($config)
    {
        $this->db = new MysqlPDO($config);
    }

    /**
     * 程序安装安装
     */
    public function install()
    {
        Out::info("start install...");
        if (file_exists(DATA_PATH . 'install.lock')) {
            Out::error("You had already installed");
        }
        if (! file_exists(DATA_PATH)) {
            mkdir(DATA_PATH, 0775, true);
        }
        if (! file_exists(TEP_PATH)) {
            mkdir(TEP_PATH, 0775, true);
        }
        if (! file_exists(LOG_PATH)) {
            mkdir(LOG_PATH, 0775, true);
        }

        $sqlFile = ROOT_PATH.'crawl.sql';
        if(!file_exists($sqlFile))
        {
              Out::error("SQL file don't exists");
        }
        $fileContent = file_get_contents($sqlFile);
        $sqlArr = array_filter(array_map('trim', explode( ';' , $fileContent )));

        foreach($sqlArr as $k => $sql)
        {
              if (empty($sql)){
                  continue;
              }
              Out::info("exec SQL:{$sql}");
             $this->db->exec($sql);
        }
        $dbConfigFile = ROOT_PATH . '/code/config/dbconfig.php';
        $config = include $dbConfigFile;
        $config['host'] .= 'dbname='.DB_NAME.';';
        $dbConfig = '<?php return '.var_export($config, true).';';
        file_put_contents($dbConfigFile, $dbConfig);
        Out::info("install databases succeed !");
        touch(DATA_PATH . 'install.lock');
        Out::info("intall succeed !");
    }

    /**
     * 下载列表页面
     *
     * @param int $siteId
     */
    public function listWrite($siteId)
    {
        $row = $this->db->find('setting', array(
            'id' => $siteId
        ));

         Out::info("loader config file ");
        if (!isset($row['cur_page'])  || empty($row['total_page'])) {
            throw new Exception('cur_page， total_page config');
            return false;
        }


        if ($row['total_page'] < $row['cur_page']) {
             Out::info("not data \n");
            return false;
        }

        $siteId = $row['id'];

        for ($i = $row['cur_page']; $i <= $row['total_page']; $i ++) {
            $urlArr[$i]['url'] = str_replace('[PAGE_NUM]', $i, $row['url']);
        }

        $result = \library\Crawl::write($urlArr, $row['project'] . '/list.txt');

        if(empty($result)){
            throw new \Exception("not data !");
            return false;
        }

        $this->insertUrl($result, $siteId);

        return $this->db->update('setting', array(
            'id' => $siteId
        ), array(
            'cur_page' => $i - 1
        ));
    }

    /**
     * 匹配列表页面内内容页面URL
     *
     * @param int $siteId
     * @return boolean
     */
    public function listRead($siteId)
    {
        $row = $this->db->find('setting', array(
            'id' => $siteId
        ));
        $filename = $row['project'] . '/list.txt';
        $callback = '\library\CrawlCallback::listWrite';
        return Crawl::read($filename, $callback, $row, $this->db);
    }

    /**
     * 匹配内容页面相关内容
     *
     * @param int $siteId
     * @return boolean
     */
    public function detailRead($siteId)
    {
        $row = $this->db->find('setting', array(
            'id' => $siteId
        ));
        $row['data'] = $this->db->findAll('setting_content', array(
            'site_id' => $siteId
        ));
        $filename = $row['project'] . '/detail.txt';
        $callback = '\library\CrawlCallback::detailWrite';
        return Crawl::read($filename, $callback, $row, $this->db);
    }

    /**
     * 清除项目数据
     *
     * @param int $siteId
     * @throws \Exception
     * @return boolean
     */
    public function clear($siteId)
    {
        $row = $this->db->find('setting', array(
            'id' => $siteId
        ));
        $this->db->delete('url', array(
            'site_id' => $siteId
        ));
        $this->db->delete('data', array(
            'site_id' => $siteId
        ));
        $this->db->delete('data_detail', array(
            'site_id' => $siteId
        ));
        $this->db->delete('data_image', array(
            'site_id' => $siteId
        ));
        $path = realpath(ROOT_PATH . DIRECTORY_SEPARATOR . 'data/' . $row['project']);

        if (! $path) {
            throw new \Exception('dir not exists:' . ROOT_PATH . DIRECTORY_SEPARATOR . 'data/' . $row['project']);
            return false;
        }
        if (IS_WIN) {
            exec("rd /s /q {$path}", $output);
        } else {
            exec("rm -rf {$path}", $output);
        }
        Out::info("[succeed] clear {$row['project']} " . \var_export($output, true) );
    }

    /**
     * 写入url表
     * @param array $data
     * @param int $siteId
     */
     protected  function insertUrl($data, $siteId){
        $result = [];
        foreach ($data as $v){
            $result[] = array(
                'url'      => $v['url'],
                'filesize' => $v['filesize'],
                'site_id'  => $siteId,
                'type'     => 1
            );
        }
        return $this->db->insertAll('url', $result);
    }

    /**
     * 执行程序
     * @param int $siteId
     */
    public function run($siteId){
        $this->listWrite($siteId); // 下载列表
        $this->listRead($siteId); // 下载内容页面
        $this->detailRead($siteId); // 提取相关内容
    }
}

