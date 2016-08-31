<?php
namespace library;
use Exception;
/**
 * 抓取
 * @author
 */
class Crawl{

	/**
	 * @var $data
	 */
	private $data = array();

	/**
	 * 存放数据目录
	 * @var string
	 */
	static $dirPath = null;


	public function __construct(){
	}

	/**
	 * 写入获取内容
	 * @param unknown $url
	 * @param unknown $dir
	 * @param unknown $filename
	 * @return number|boolean
	 */
	public static function write($url, $filename, $sleepSeconds=0){

	    if ($sleepSeconds > 0) sleep($sleepSeconds);
		$con = self::getResponse($url);
		self::getDir();
		if (empty($con)){
			die("error: file is empty \n");
		}

        $path = dirname(self::$dirPath.$filename);

        if (!file_exists($path)){
            mkdir($path, 0775, true);
        }

		$fileSize             = strlen($con);
		$data['url']          = str_pad($url, 255);
		$data['content_leng'] = pack("L", $fileSize);
		$data['content']      = $con;

		$res = file_put_contents(self::$dirPath.'/'.$filename, $data, FILE_APPEND|LOCK_EX);
		if ($res){
			echo "[succeed] {$data['url']} \n";
			return $fileSize;
		} else {
			echo "[defeated] {$data['url']} \n";
			return false;
		}
	}

    /**
     *
     * @return string
     */
	public static function getDir(){
	    if (is_null(self::$dirPath)){
            self::$dirPath = str_replace("\\","/",dirname('__FILE__')."/data/");
	    }
		return  self::$dirPath;
	}


	/**
	 * @param string $filename
	 */
	public static function read($filename,  $callback, $row, $db){
	    self::getDir();
	    $filename =  self::$dirPath.$filename;
	    if (!file_exists($filename)){
            throw new \Exception("file not exists:{$filename}");
	    }
		$handle = fopen($filename, "rb");
		$data = array();
		for ($i = 0; ; $i++) {
			if (feof($handle)) {
				break;
			}
			$data[$i]['url'] = fread($handle, 255);
			$contentLen = fread($handle, 4);
			if(empty($contentLen))
			{
				echo "error: file is not normal termination! 01 \n";
				break;
			}
			$aConLeng = unpack("Ldata", $contentLen);
			$conLeng = $aConLeng['data'];
			if ($conLeng == 0) {
				echo "error: file is not normal termination! 02  \n";
				break;
			}

			$data[$i]['content'] = fread($handle, $conLeng);
			call_user_func_array($callback,  array($row, $db, $data[$i]));
			//echo "[succeed] {$data[$i]['url']} \n";
			unset($data[$i]);
		}
		return true;
	}



	/**
	 * Get response from search engine
	 *
	 * @param string $url The URL
	 * @param int $timeout The connection timeout (in seconds)
	 * @return string
	 */
 	public	static function getResponse($url, $timeout = 20)
	{
		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			if(isset($_SERVER['HTTP_USER_AGENT']))
			{
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
			$res =  curl_exec($ch);
			try
			{
				if(curl_errno($ch))
				{
					throw new Exception('Curl error: ' . curl_error($ch));
				}
			}
			catch (Exception $e)
			{
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
			curl_close ($ch) ;
			return $res;
		} else {
			return file_get_contents($url);
		}
	}
}