<?php
/**
 * User: derekcheng
 * Date: 2016/10/12
 * Time: 17:11
 */
namespace TSF\Core;

use TSF\Facade\App;
use TSF\Exception\Core\LogException;

class Log
{
    protected static $logDirPath;           //日志路径
    protected static $logFileName;          //日志文件名称
    protected static $logFilePath;            //日志文件完整路径
    protected static $logFileFd;
    protected static $logWorkLevel;         //日志生效的级别，低于此级别的日志不输出
    protected static $logDecorator;         //是否自动染色 0 不自动染色 1 自动染色
    protected static $logServerIp;          //是否带本机IP 0 带IP 1 不带IP
    protected static $skipClasses = [Log::class];

    //日志级别
    const LOG_LEVEL_ERROR   = 1;          //严重错误
    const LOG_LEVEL_WARNING = 2;          //警告
    const LOG_LEVEL_NOTICE  = 3;          //注意
    const LOG_LEVEL_INFO    = 4;          //流水
    const LOG_LEVEL_DEBUG   = 5;          //调试

    /**
     * [init 初始化参数]
     * @Author   derekcheng
     * @DateTime 2016-10-12T17:48:26+0800
     * @param    [array]                   $params [参数数组]
     * @return   [Null]                            [无返回]
     */
    public static function init($config)
    {
        self::$logDecorator = array_get($config, 'logDecorator', true);
        self::$logServerIp  = array_get($config, 'logServerIp', '');
        self::$logWorkLevel = array_get($config, 'logWorkLevel', self::LOG_LEVEL_WARNING);
        self::$logDirPath   = array_get($config, 'logPath', App::facade()->getBasePath() . '/Storage/Log');
        self::$logFileName  = array_get($config, 'logFileName', 'TSF');
        self::$logFilePath  = self::$logDirPath . '/' . self::$logFileName . '-' . date('Ymd') . '.log';
        self::$logFileFd    = fopen(self::$logFilePath, 'a');
        if (self::$logFileFd === false) {
            throw new LogException('Cannot open ' . self::$logFilePath . ' for write.');
        }
    }

    /**
     * [debug Log]
     * @Author   derekcheng
     * @DateTime 2016-10-12T17:19:14+0800
     * @param    [string]                   $message  [日志内容]
     * @param    [string]                   $line     [行号]
     * @param    [string]                   $function [当前方法名]
     * @param    [string]                   $class    [当然类名]
     * @return   [NULL]                               [无返回]
     */
    public static function debug($message, $line = '', $function = '', $class = '')
    {
        self::log(self::LOG_LEVEL_DEBUG, $message, $line, $function, $class);
    }

    public static function info($message, $line = '', $function = '', $class = '')
    {
        self::log(self::LOG_LEVEL_INFO, $message, $line, $function, $class);
    }

    public static function notice($message, $line = '', $function = '', $class = '')
    {
        self::log(self::LOG_LEVEL_NOTICE, $message, $line, $function, $class);
    }

    public static function warning($message, $line = '', $function = '', $class = '')
    {
        self::log(self::LOG_LEVEL_WARNING, $message, $line, $function, $class);
    }

    public static function error($message, $line = '', $function = '', $class = '')
    {
        self::log(self::LOG_LEVEL_ERROR, $message, $line, $function, $class);
    }


    protected static function log($level, $message, $line, $function, $class)
    {
        if ($level > self::$logWorkLevel) {
            return;
        }

        if (self::$logDecorator) {
            $message = self::decorator($message);
        } else {
            $message = "[{$class}::{$function}:{$line}] {$message}\n";
        }

        $date = date('Ymd');
        if ($date > substr(self::$logFilePath, -12, 8)) {
            self::$logFilePath  = self::$logDirPath . '/' . self::$logFileName . '-' . date('Ymd') . '.log';
            self::$logFileFd = fopen(self::$logFilePath, 'a');
        }
        if (self::$logFileFd == false) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $message = "[{$date}] {$message}";
        if (!empty(self::$logServerIp)) {
            $message = '[' . self::$logServerIp . ']' . $message;
        }
        switch ($level) {
            case self::LOG_LEVEL_DEBUG:
                $message = '[DEBUG]' . $message;
                break;
            case self::LOG_LEVEL_INFO:
                $message = '[INFO]' . $message;
                break;
            case self::LOG_LEVEL_NOTICE:
                $message = '[NOTICE]' . $message;
                break;
            case self::LOG_LEVEL_WARNING:
                $message = '[WARNING]' . $message;
                break;
            case self::LOG_LEVEL_ERROR:
                $message = '[ERROR]' . $message;
                break;
            default:
                break;
        }
        fwrite(self::$logFileFd, $message);
    }

    //自动染色
    protected static function decorator($message)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $message = "]\n$message";
        foreach ($trace as $frame) {
            if (self::skipFrame($frame)) {
                continue;
            }
            $message = "{$frame['class']}{$frame['type']}{$frame['function']}" . $message;
            if (!empty($frame['file'])) {
                $message = "\n{$frame['file']}:{$frame['line']} " . $message;
            }
        }
        $message = "[stack:\n{$message}\n";
        return $message;
    }

    protected static function skipFrame($frame)
    {
        return in_array($frame['class'], self::$skipClasses);
    }
}
