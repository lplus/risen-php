<?php
namespace risen\base;

#trace
use risen\Trace;
#endtrace


/**
 * Class Application
 * @package risen
 */
class Application
{
    /**
     *
     */
    private function __construct(){}
    private static $layoutHandler = null;
    static $applicationName = '';
    static $layoutChildProperty = '';
    static $classInfo = '';

    static function setLayout($layoutChildProperty, RequestHandler $layoutHandler)
    {
        self::$layoutChildProperty = $layoutChildProperty;
        self::$layoutHandler = $layoutHandler;
    }
    /**
     * @param string $appName 应用名，应用程序目录的名字
     * @param callable $userMapFunction 自定义URL映射的函数，如果函数返回空则使用默认规则,
     */
    static function run($appName = 'app', callable $userMapFunction = null)
    {
        self::$applicationName = $appName;

        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            parse_str(file_get_contents('php://input', false , null, -1 , $_SERVER['CONTENT_LENGTH'] ), $_PUT);
            $_REQUEST = array_merge($_REQUEST, $_PUT);
        }

        $configFile = isset($_SERVER['APP_ENV']) ?  "$appName/Config{$_SERVER['APP_ENV']}.php": '$appName/Config.php';
        if (is_file($configFile)) {
            include $configFile;
        }

        $urlPart = self::urlMapping($userMapFunction);
        self::$classInfo = $urlPart;
        $handlerClass = "{$appName}\\handler{$urlPart}";

        if (class_exists($handlerClass)) {
            $handler = new $handlerClass;

#trace
            if (!($handler instanceof RequestHandler)) {
                Trace::appendError([
                    'errstr' => "$handlerClass must be sub class of risen\\base\\RequestHandler"
                ]);
            }
#endtrace

            $handler->handle();
        }
        else {
            $handlerClass404 = "$appName\\handler\\NotfoundHandler";
            header("HTTP/1.1 404 Not Found");
            if (class_exists($handlerClass404)) {
                $handler404 = new $handlerClass404;
                $handler404->setAutoDisplay();
                $handler404->handle();
            }
#trace
            else {
                echo "<h1>404 Not Found</h1>\n";
                echo "<hr/>\n";
                echo "class $handlerClass not found\n";
            }
            Trace::appendError(array(
                'errstr' => "class $handlerClass not found"
            ));
#endtrace
        }
#trace
        Trace::$allErrorsCanHandle = true;
#endtrace
    }

    /**
     * @return mixed|string 返回与URL对应的类名部分，与前缀 $appName\handler 拼接后为handler的完整类名
     */
    private static function urlMapping(callable $userFunc = null)
    {

        $pathInfo = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME']: $_SERVER['PHP_SELF'];

        $dotPos = strpos($pathInfo, '.');
        if ($dotPos !== false) {
            $pathInfo = substr($pathInfo, 0, $dotPos);
        }

        if ($userFunc) {
            $handlerClass = $userFunc($pathInfo);
            if ($handlerClass === false) {
                return;
            }
            if (!empty($handlerClass)) {
                return $handlerClass;
            }
        }

        if (strrpos($pathInfo, '-')) {
            $args = explode('-', $pathInfo);
            $pathInfo = array_shift($args);
            foreach($args as $k => $arg)
            {
                $_REQUEST[$k] = $arg;
            }
        }

        $pathInfo = str_replace('/', '\\', $pathInfo);

        //if ($pathInfo[strlen($pathInfo) - 1] == '\\') {
        //    $pathInfo .= 'IndexHandler';
        //}
        //else {
            $lastDelimiter = strrpos($pathInfo, '\\');
            $pathInfo[$lastDelimiter + 1] = strtoupper($pathInfo[$lastDelimiter + 1]);
            $pathInfo .= 'Handler';
        //}

        return $pathInfo;
    }
}
