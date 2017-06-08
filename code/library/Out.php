<?php
namespace crawl\library;

class Out
{

        /**
         * Outputs an array of info
         *
         * @param array $info
         */
         public static function info($info)
        {
              self::print($info, 'info');
        }

        /**
         * Outputs an array of error
         *
         * @param array $error
         */
        public static function error($error)
        {
            if(is_array($error)){
                foreach ($error as $v){
                      self::print($v, 'error');
                }
            } else {
                 self::print($error, 'error');
            }
        }

        /**
         * Outputs any warning found
         *
         * @param array $warnings
         */
       public static function warning($warnings)
        {
            self::print($warnings, 'info');
        }



   /**
    * 打印文字，加颜色
    * @param unknown $text
    * @param unknown $color
    * @param string $newLine
    */
    public static function print($text, $color = null, $newLine = true)
    {
        $use_ansi = (
                (DIRECTORY_SEPARATOR == '\\')
                    ? (false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI'))
                    : (function_exists('posix_isatty') && posix_isatty(1))
            );

        $styles = array(
            'success' => "\033[0;32m%s\033[0m",
            'error' => "\033[31;31m%s\033[0m",
            'info' => "\033[33;33m%s\033[0m"
        );

        $format = '%s';

        if (isset($styles[$color]) && $use_ansi) {
            $format = $styles[$color];
        }

        if ($newLine) {
            $format .= PHP_EOL;
        }

        printf($format, $text);
    }

}

