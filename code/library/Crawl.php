<?php
namespace crawl\library;

use crawl\library\Out;
use Exception;

/**
 * 抓取文件
 * 通过Curl,下载到本地
 * @author wxxiong6@gmail.com
 */
class Crawl
{

    /**
     * url 字符长度
     * @var string
     */
    const URL_LENGTH = '125';

    /**
     *
     * @var $data
     */
    private $data = [];

    /**
     * 存放数据目录
     * @var string
     */
    static $dirPath = DATA_PATH;

    public function __construct()
    {}

    /**
     * 通过CURL下载页面，写入文件
     *
     * @param array $url           需要下载页面URL
     * @param string $filename     写入文件名称
     * @param string $sleepSeconds 暂停时间 （秒）
     * @return number|boolean
     */
    public static function write(array $url, $filename, $sleepSeconds = 0)
    {
        if ($sleepSeconds > 0)
            sleep($sleepSeconds);

        self::getDir();

        $path = dirname(self::$dirPath . $filename);
        if (! file_exists($path)) {
            mkdir($path, 0775, true);
        }

        $filename = self::$dirPath . '/' . $filename;

        /**
         * 获取网页数据
         *
         * @var Ambiguous $htmlData
         */
        $htmlData = self::multiCurl($url);
        if (empty($htmlData)) {
            Out::error("error: file is empty 1 " . var_export($url, true));
            return false;
        }
        if (! is_array($htmlData))
            $htmlData = (array) $htmlData;

        $result = [];
        $handle = fopen($filename, "w+");
        foreach ($htmlData as $k => $v) {
            $url = $v['url'];
            $con = $v['data'];
            $error = $v['error'];
            if (empty($con)) {
                Out::error("error: file is empty {$url}, errorInfo: {$error} ");
                continue;
            }

            $filesize = strlen($con);
            $data['url'] = str_pad($url, self::URL_LENGTH);
            $data['content_leng'] = pack("L", $filesize);
            $data['content'] = $con;
            $writeResult = fwrite($handle, $data['url']);
            $writeResult = fwrite($handle, $data['content_leng']);
            $writeResult = fwrite($handle, $data['content']);

            $result[$k]['filesize'] = $filesize;
            $result[$k]['url'] = $url;

            if ($writeResult) {
                Out::info("[download succeed] {$url}");
                ;
            } else {
                Out::error("[download defeated] {$url}");
            }
        }
        fclose($handle);
        return $result;
    }

    /**
     * 获取目录
     *
     * @return string
     */
    public static function getDir()
    {
        return self::$dirPath;
    }

    /**
     * 设置目录
     */
    public static function setDir($dirPath){
        self::$dirPath = $dirPath;
    }

    /**
     * 读取数据
     * @param string $filename
     */
    public static function read($filename, $callback, $row, $db)
    {
        self::getDir();
        $filename = self::$dirPath . $filename;
        if (! file_exists($filename)) {
            throw new \Exception("file not exists:{$filename}");
        }
        $handle = fopen($filename, "rb");
        $data = array();
        $i = 0;
        while (! feof($handle)) {
            $data[$i]['url'] = trim(fread($handle, self::URL_LENGTH));
            $contentLen = fread($handle, 4);
            if (empty($contentLen)) {
                Out::error("{$data[$i]['url']} error: {$filename} file is not normal termination! 01 ");
                break;
            }
            $aConLeng = unpack("Ldata", $contentLen);
            $conLeng = $aConLeng['data'];
            if ($conLeng <= 0) {
                var_dump($kb);
                Out::error("error: file is not normal termination! 02  ");
                break;
            }

            $data[$i]['content'] = fread($handle, $conLeng);
            call_user_func_array($callback, array(
                $row,
                $db,
                $data[$i]
            ));
            Out::info("[match succeed] {$data[$i]['url']} ");
            unset($data[$i]);
            $i ++;
        }
        fclose($handle);
        return true;
    }

    /**
     * Get response from search engine
     *
     * @param string $url
     *            The URL
     * @param int $timeout
     *            The connection timeout (in seconds)
     * @return string
     */
    public static function getResponse($url, $timeout = 20)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            } else {
                $_SERVER['HTTP_USER_AGENT'] = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36";
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ((ini_get('open_basedir') == '') && (ini_get('safe_mode') == 'Off')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            $res = curl_exec($ch);
            try {
                if (curl_errno($ch)) {
                    throw new Exception('Curl error: ' . curl_error($ch));
                }
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
            curl_close($ch);
            return $res;
        } else {
            return file_get_contents($url);
        }
    }

    /**
     * 多线程抓取
     *
     * @param array $res
     * @param string $options
     * @return boolean|string
     */
    public static function multiCurl(array $res, $options = "")
    {
        if (empty($res))
            return false;

        if (! is_array($res)) {
            Out::error("params is Array");
            return False;
        }

        $handles = array();

        if (! $options) // add default options
            $options = array(
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36",
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_TIMEOUT => 60, // 秒
                CURLOPT_CONNECTTIMEOUT => 25
            );

            // add curl options to each handle
        foreach ($res as $k => $row) {
            if (empty($row['url']))
                continue;
            $ch{$k} = curl_init();
            $options[CURLOPT_URL] = $row['url'];
            $opt = curl_setopt_array($ch{$k}, $options);
            $handles[$k] = $ch{$k};
        }
        $mh = curl_multi_init();
        // add handles
        foreach ($handles as $k => $handle) {
            $err = curl_multi_add_handle($mh, $handle);
        }

        $running_handles = null;

        do {
            curl_multi_exec($mh, $running_handles);
            curl_multi_select($mh);
        } while ($running_handles > 0);

        foreach ($res as $k => $row) {
            $res[$k]['error'] = curl_error($handles[$k]);
            if (! empty($res[$k]['error']))
                $res[$k]['data'] = '';
            else
                $res[$k]['data'] = curl_multi_getcontent($handles[$k]); // get results
                                                                            // close current handler
            curl_multi_remove_handle($mh, $handles[$k]);
        }
        curl_multi_close($mh);
        return $res; // return response
    }

    /**
     * 把相当路径处理在绝对路径
     * @param string $url    url
     * @param String $source 当前页面完整的URL
     * @return unknown|boolean|string
     */
    public static function formatUrl($url, $source)
    {
        if(strpos($url, '://') !== FALSE)  return $url;
        $urlPart = parse_url($source);
        if($urlPart == FALSE) return FALSE;

        $baseUrl = $urlPart['scheme'] . '://' . $urlPart['host'] . (isset($urlPart['port']) ? ':' . $urlPart['port'] : '');
        $basePath = isset($urlPart['path']) ? $urlPart['path'] : '/' ;

        if(strpos($url, "//") === 0){
            $url = $urlPart['scheme'] . '://' . substr($url,2);
        } elseif(strpos($url, '/') === 0){
             $url = $baseUrl . $url;
        } elseif(strpos($url, './') === 0){
             $url = $baseUrl.$basePath. ltrim($url, './');
        } elseif(strpos($url, '..') === 0){ //多级目录
            //路径中 找出../../a.jpg 分割成array
            $pathArr = explode('../', $url);
            //删除a.jpg部分,只保留 ../../ path中分
            $filePath = array_pop($pathArr);
            $pathLevel = count($pathArr);

            $basePathArr = explode('/', ltrim($basePath,'/'));
            for($i=0; $i<$pathLevel; $i++){
                array_pop($basePathArr);
            }
            $url = $baseUrl . join('/', $basePathArr) .$filePath;
        }
        return $url;
    }

}
