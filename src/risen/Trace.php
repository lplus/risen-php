<?php #release
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/5/24
 * Time: 上午11:09
 */
namespace risen;

class Trace
{
    private static $__data = array();
    static $allErrorsCanHandle = false;

    const SHOW_CONSOLE = 1;
    const SHOW_WINDOW = 2;
    const SHOW_BOTH = 3;

    static $showMask = 1;

    static function enable($showMask = 1)
    {
        ob_start();
        self::$showMask = $showMask;

        self::$__beginTime = microtime(true);
        self::$__data['globals']['$_SERVER'] = $_SERVER;
        self::$__data['globals']['$_GET'] = $_GET;
        self::$__data['globals']['$_POST'] = $_POST;
        self::$__data['globals']['$_COOKIE'] = $_COOKIE;
        self::$__data['globals']['$_ENV'] = $_ENV;
        self::$__data['globals']['$_FILES']= $_FILES;


        //ini_set('display_error', 0);
        //error_reporting(0);

        if ($_SERVER['REQUEST_URI'] == '/__risen_trace.js') {
            self::__outputJs();
            exit;
        }

        set_exception_handler(array('risen\Trace', 'exception'));
        set_error_handler(array('risen\Trace', '__errorHandler'));
        register_shutdown_function('risen\Trace::__shutdown');

        $aliasFile = __DIR__ . "/trace/class_alias.php";
        if (is_file($aliasFile)) {
            include $aliasFile;
        }
		include __DIR__ . '/trace/user_trace_func.php';
    }



    static function __errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        self::$__data['error'][] = array(
            'errstr' => $errstr . "\nin {$errfile} on line {$errline}",
            'type' => 'php',
        );
    }

    static function exception($e)
    {
        self::$__data['error'][] = array(
            'errstr' => $e->getMessage() . ".\nin ". $e->getFile() . "on line " . $e->getLine(),
            'type' => 'php',
        );
    }

    static function __shutdown()
    {
        if (isset($_SESSION)) {
            self::$__data['globals']['$_SESSION'] = $_SESSION;
        }

        (defined("DEBUG_OUTPUT") && !DEBUG_OUTPUT ) && die;
        self::$__data['statics'] = array(
            'time' => round(microtime(true) - self::$__beginTime, 4),
            'mem' => memory_get_usage(true)
        );

        if (self::$allErrorsCanHandle === false) {
            $err = error_get_last();

            if (!empty($err)) {
                $msg = 'Fatal Error ' . $err['message'];
                if (!empty($err['file'])) {
                    $msg .= " in " . $err['file'];
                }
                if (!empty($err['line'])) {
                    $msg .= " on line ". $err['line'];
                }
                self::$__data['error'][] = array(
                    'errstr' => $msg,
                    'type' => 'php',
                );
            }
        }

        $output = ob_get_clean();
        $json = json_encode(self::$__data);
//        var_dump($_SERVER);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header("__risen_trace_json: " . $json);
            echo $output;
        }
        else {
            echo $output;
            $json = json_encode(self::$__data);
            $showMask = self::$showMask;
            echo <<<script
<script type="text/javascript">
__risen_trace_option = {
    data: $json,
    showMask: $showMask,
    handle: null
};
</script>
<script src="/__risen_trace.js" type="text/javascript"></script>

script;

        }
    }

    private static $__beginTime;



    static function appendError($info)
    {
        if (isset($info['backtrace'])) {
            $backtrace = array();
            foreach($info['backtrace'] as $k => &$value) {
                if (isset($value['file']) && isset($value['line'])) {
                    if (strpos($value['file'], __DIR__) === 0) {
                        continue;
                    }
                    $backtrace[] = array(
                        'file' => $value['file'],
                        'line' => $value['line']
                    );
                }
            }

            $info['backtrace'] = $backtrace;
        }
        self::$__data['error'][] = $info;
    }

    static function appendInfo($info)
    {
        self::$__data['info'][] = $info;
    }

    static function appendSql($sql, $time, $explain)
    {
        $backtrace_ = debug_backtrace();

        $backtrace = array();

        $i=0;
        foreach($backtrace_ as &$value)
        {
            if (empty($value['file'])) {
                continue;
            }
            if (strpos($value['file'], __DIR__) === 0) {
                continue;
            }
            $backtrace[$i]['file'] = $value['file'];
            $backtrace[$i]['line'] = $value['line'];
            $backtrace[$i]['function'] = $value['function'];
            $backtrace[$i]['class'] = $value['class'];
            $i++;
        }

        self::$__data['sql'][] = array(
            'sql'=>$sql,
            'time'=> round($time, 4),
            'explain' => $explain,
            'backtrace' => $backtrace
        );
    }

    static function toJson()
    {
        return json_encode(self::$__data);
    }

    static function __outputJs()
    {
        header("Content-Type:application/javascript");

        echo file_get_contents(__DIR__ . '/trace/__risen_trace.js');
    }
}

function D(&$var)
{

}

function DR(&$arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}
