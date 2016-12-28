<?php
/**
 * 日志类
 * @author    wxxiong@gmail.com
 * @version   v1.1
 */
class Log
{
    const LEVEL_TRACE   = 'trace';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR   = 'error';
    const LEVEL_INFO    = 'info';
    const LEVEL_PROFILE = 'profile';

    /**
     * @var integer how many messages should be logged before they are flushed to destinations.
     * Defaults to 10,000, meaning for every 10,000 messages
     */
    public $autoFlush=10000;

    /**
     * @var array log messages
     */
    private $_logs=array();

    /**
     * @var integer number of log messages
     */
    private $_logCount=0;

    /**
     * @var array log levels for filtering (used when filtering)
     */
    private $_levels;

    /**
     * @var array log categories for filtering (used when filtering)
     */
    private $_categories;

    /**
     * @var integer maximum log file size
     */
    private $_maxFileSize=1024; // in KB

    /**
     * @var integer number of log files used for rotation
     */
    private $_maxLogFiles=5;

    /**
     * @var string directory storing log files
     */
    private $_logPath;

    /**
     * @var string log file name
     */
    private $_logFile='application.log';

    /**
     * @var object
     */
    private static $_instance;


    public function __construct(){
        if($this->getLogPath()===null)
            $this->setLogPath(__DIR__);
    }

    /**
     * 获取对象
     * @return object
     */
    public static function getInstance(){
        if (!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * @return string directory storing log files. Defaults to application runtime path.
     */
    public function getLogPath()
    {
        return $this->_logPath;
    }

    /**
     * @param string $value directory for storing log files.
     * @throws CException if the path is invalid
     */
    public function setLogPath($value)
    {
        $this->_logPath=realpath($value);
        if($this->_logPath===false || !is_dir($this->_logPath) || !is_writable($this->_logPath))
            throw new Exception('logPath "{path}" does not point to a valid directory.
			 Make sure the directory exists and is writable by the Web server process.');
    }

    /**
     * @return string log file name. Defaults to 'application.log'.
     */
    public function getLogFile()
    {
        return $this->_logFile;
    }

    /**
     * @param string $value log file name
     */
    public function setLogFile($value)
    {
        $this->_logFile=$value;
    }

    /**
     * @return integer maximum log file size in kilo-bytes (KB). Defaults to 1024 (1MB).
     */
    public function getMaxFileSize()
    {
        return $this->_maxFileSize;
    }

    /**
     * @param integer $value maximum log file size in kilo-bytes (KB).
     */
    public function setMaxFileSize($value)
    {
        if(($this->_maxFileSize=(int)$value)<1)
            $this->_maxFileSize=1;
    }

    /**
     * @return integer number of files used for rotation. Defaults to 5.
     */
    public function getMaxLogFiles()
    {
        return $this->_maxLogFiles;
    }

    /**
     * @param integer $value number of files used for rotation.
     */
    public function setMaxLogFiles($value)
    {
        if(($this->_maxLogFiles=(int)$value)<1)
            $this->_maxLogFiles=1;
    }

    /**
     * 替代WarnLog。旧函数参数太多，使用不方便
     *
     * @param string $value1
     * @param string $value2
     */
    public static function warn ($value)
    {
        return self::write($value, self::LEVEL_WARNING, self:: getLogInfo());
    }

    /**
     * 调试日志
     * @param string $value1
     * @param string $value2
     */
    public static function info ($value)
    {
        return self::write($value, self::LEVEL_INFO, self:: getLogInfo());
    }

    /**
     * 错误日志
     * @param string $value1
     */
    public static function error($value)
    {
        return self::write($value, self::LEVEL_ERROR, self:: getLogInfo());
    }

    /**
     * Logs a message.
     * @param string $message message to be logged
     * @param string $level level of the message (e.g. 'Trace', 'Warning', 'Error').
     * @param string $category category of the message .
     * @see getLogs
     */
    public static function write($message,  $level='info', $logInfo='')
    {
        $obj = self::getInstance();
        $obj->_logs[]=array($message,$level,microtime(true), $logInfo);
        $obj->_logCount++;
        if($obj->autoFlush>0 && $obj->_logCount>=$obj->autoFlush){
            $obj->flush();
            //内存超过限制
        } elseif(intval(memory_get_usage()/1024) >= $obj->_maxFileSize){
            $obj->flush();
        }
    }

    /**
     * Removes all recorded messages from the memory.
     */
    public function flush()
    {
        $this->onFlush();
        $this->_logs=array();
        $this->_logCount=0;
    }
    /**
     * Raises an <code>onFlush</code> event.
     * @param CEvent $event the event parameter
     * @since 1.1.0
     */
    public function onFlush()
    {
        $this->processLogs($this->_logs);
    }

    /**
     * Formats a log message given different fields.
     * @param string $message message content
     * @param integer $level message level
     * @param string $category message category
     * @param integer $time timestamp
     * @return string formatted message
     */
    protected function formatLogMessage($message,$level,$time,$logInfoArr)
    {
        //获取IP
        $ipstr = '0.0.0.0';
        if (isset($_SERVER["SERVER_ADDR"])){
            $ipstr = $_SERVER["SERVER_ADDR"];
        }
        return $this->udate('y-m-d H:i:s.u', $time)." <".$level.">: [".$logInfoArr['func']."] [".getmypid()."] [".$ipstr."] ".
            $logInfoArr['file']." line (".$logInfoArr['line']."):". $message ." \n";
    }

    /**
     * Saves log messages in files.
     * @param array $logs list of log messages
     */
    protected function processLogs($logs)
    {
        $logFile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
        if(@filesize($logFile)>$this->getMaxFileSize()*1024)
            $this->rotateFiles();
            $fp=@fopen($logFile,'a');
            @flock($fp,LOCK_EX);
            foreach($logs as $log)
                @fwrite($fp,$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]));
                @flock($fp,LOCK_UN);
                @fclose($fp);
    }

    /**
     * Rotates log files.
     */
    protected function rotateFiles()
    {
        $file=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
        $max=$this->getMaxLogFiles();
        for($i=$max;$i>0;--$i)
        {
            $rotateFile=$file.'.'.$i;
            if(is_file($rotateFile))
            {
                // suppress errors because it's possible multiple processes enter into this section
                if($i===$max)
                    @unlink($rotateFile);
                    else
                        @rename($rotateFile,$file.'.'.($i+1));
            }
        }
        if(is_file($file))
            @rename($file,$file.'.1'); // suppress errors because it's possible multiple processes enter into this section
    }


    /**
     * 返回 文件名、行号和函数名
     * @param $skipLevel
     */
    private static function getLogInfo ($skipLevel = 1)
    {
        $trace_arr = debug_backtrace();
        for ($i = 0; $i < $skipLevel; $i ++)
        {
            array_shift($trace_arr);
        }
        $tmp_arr1 = array_shift($trace_arr);
        if (! empty($trace_arr))
        {
            $tmp_arr2 = array_shift($trace_arr);
        }
        else
        {
            $tmp_arr2 = array(
                'function' => "MAIN" //主流程 __MAIN__
            );
        }
        if (isset($tmp_arr2['class'])) // 类的方法
        {
            $func = $tmp_arr2['class'] . $tmp_arr2['type'] . $tmp_arr2['function'];
        }
        else
        {
            $func = $tmp_arr2['function'];
        }
        if(!empty($tmp_arr1['file'])){
            $path = pathinfo($tmp_arr1['file']);
            $tmp_arr1['file'] = $path['basename'];
        }
        return array(
            'line' => $tmp_arr1['line'] ,
            'file' => $tmp_arr1['file'] ,
            'func' => $func
        );
    }

    /**
     *  毫秒
     * @param string $strFormat
     * @param unknown $uTimeStamp
     * @return string
     */
    private function udate($strFormat = 'u', $uTimeStamp = null)
    {
        // If the time wasn't provided then fill it in
        if (is_null($uTimeStamp))
        {
            $uTimeStamp = microtime(true);
        }
        // Round the time down to the second
        $dtTimeStamp = floor($uTimeStamp);
        // Determine the millisecond value
        $intMilliseconds = round(($uTimeStamp - $dtTimeStamp) * 1000000);
        // Format the milliseconds as a 6 character string
        $strMilliseconds = str_pad($intMilliseconds, 6, '0', STR_PAD_LEFT);
        // Replace the milliseconds in the date format string
        // Then use the date function to process the rest of the string
        return date(preg_replace('`(?<!\\\\)u`', $strMilliseconds, $strFormat), $dtTimeStamp);
    }

    public function __destruct(){
        if($this->_logCount > 0)
            $this->flush();
    }
}
