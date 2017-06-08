<?php
namespace crawl\library;
use crawl\library\Out;
use Exception;

/**
 * 抓取
 *
 * @author
 *
 */
class Crawl
{

    /**
     *
     * @var $data
     */
    private $data = array();

    /**
     * 存放数据目录
     *
     * @var string
     */
    static $dirPath = null;

    public function __construct()
    {}

    /**
     * 写入获取内容
     *
     * @param array $url
     * @param unknown $dir
     * @param unknown $filename
     * @return number|boolean
     */
    public static function write($url, $filename, $sleepSeconds = 0)
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
         * @var Ambiguous $htmlData
         */
        $htmlData = self::multiCurl($url);
        if (empty($htmlData)){
            Out::error("error: file is empty 1 ".var_export($url, true));
            return  false;
        }
        if (!is_array($htmlData))
            $htmlData = (array) $htmlData;

        $result = [];
        foreach ($htmlData as $k => $v){
            $url = $v['url'];
            $con = $v['data'];
            $error = $v['error'];
            if (empty($con)) {
                Out::error("error: file is empty {$url}, errorInfo: {$error} ");
                continue;
            }

            $filesize             = strlen($con);
            $data['url']          = str_pad($url, 255);
            $data['content_leng'] = pack("L", $filesize);
            $data['content']      = $con;

            $writeResult          = file_put_contents($filename, $data, FILE_APPEND | LOCK_EX);
            $result[$k]['filesize']   = $filesize;
            $result[$k]['url']        = $url;

            if ($writeResult) {
                Out::info("[download succeed] {$url}");;
            } else {
                 Out::info("[download defeated] {$url}");
            }
        }
        return $result;
    }

    /**
     * 数据目录
     *
     * @return string
     */
    public static function getDir()
    {
        if (is_null(self::$dirPath)) {
            self::$dirPath = DATA_PATH;
        }
        return self::$dirPath;
    }

    /**
     * 读取数据
     *
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
        while (!feof($handle)) {

            $data[$i]['url'] = trim(fread($handle, 255));
            $contentLen      = fread($handle, 4);
            if (empty($contentLen)) {
                 Out::error("{$data[$i]['url']} error: file is not normal termination! 01 ");
                break;
            }
            $aConLeng = unpack("Ldata", $contentLen);
            $conLeng  = $aConLeng['data'];
            if ($conLeng == 0) {
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
            $i++;
        }
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
     * @param unknown $res
     * @param string $options
     * @return boolean|string
     */
    public static function multiCurl($res, $options = "")
    {
        if (empty($res)) return false;

        if (!is_array($res)){
            Out::error( "params is Array");
            return False;
        }

        $handles = array();

        if (! $options) // add default options
            $options = array(
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36",
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_TIMEOUT => 60,    //秒
                CURLOPT_CONNECTTIMEOUT => 25
            );

            // add curl options to each handle
        foreach ($res as $k => $row) {
            if (empty($row['url'])) continue;
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
}
