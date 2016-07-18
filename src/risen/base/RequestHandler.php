<?php
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/5/9
 * Time: 下午7:32
 */

namespace risen\base;
#trace
use risen\Trace;
#endtrace

class RequestHandler
{
    protected $autoDisplay = true;
    protected $templateFile = '';

    function setAutoDisplay($auto = true)
    {
        $this->autoDisplay = $auto;
    }

    function partial($tplName)
    {
        include Application::$applicationName . '/template' . $tplName;
    }

    function setTemplate($tplFile)
    {
        $this->templateFile = $tplFile;
    }

    static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function redirectJs($url)
    {
        echo <<<html
<script type="text/javascript">
    window.location.href = "$url";
</script>
html;
        exit(0);
    }

    function redirectHeader($url,$permanently = false)
    {
        if (!headers_sent()) {
            if ($permanently) {
                header('HTTP/1.1 301 Moved Permanently');
            }
            header("Location: $url");
        }
    }

    function entrust(RequestHandler $handler)
    {
        $handler->handle();
        exit(0);
    }

    function handleAbort() {
        $this->hasHandle = true;
    }

    private $hasHandle = false;
    function handle()
    {
        if ($this->hasHandle) {
            return;
        }
        $this->hasHandle = true;
        $result = null;
        if (method_exists($this, 'init')) {
            $this->init();
        }

        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            if (method_exists($this, 'onGet')) {
                $result = $this->onGet();
            }
        }
        else {
            $method = 'on' . ucfirst(strtolower($_SERVER['REQUEST_METHOD']));

            if (method_exists($this, $method)) {
                $result = $this->$method();
            }
            else {
                header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed");
#trace

                echo "<h1>405 Not Allowed</h1>\n";
                echo "<hr/>\n";
                echo "method {$_SERVER['REQUEST_METHOD']} not allowed\n";

                Trace::appendError(array(
                    'errstr' => "method {$method} not exists in class " . get_class($this)
                ));
#endtrace

            }
        }

        if (method_exists($this, 'finish')) {
            $this->finish();
        }

        if ($result !== null && $result !== "") {
            if (is_string($result) || is_numeric($result)) {
                echo $result;
            }
            else{ // 目前只当做json处理，xml之类的以后可以考虑
#trace
				Trace::setResponseType(Trace::TYPE_JSON);
#endtrace
                if (!headers_sent()) {
                    header("Content-Type:text/json");
                }
                echo json_encode($result);
            }
            return;
        }
		else if ($this->autoDisplay) {
            $this->display();
        }
    }

    function display()
    {
//        static $hasDisplay = false;
//        if ($hasDisplay) {
//            return;
//        }

        $templateFile = ($this->templateFile == '') ?
            str_replace(array('handler\\', '\\'), array('template/', '/'), get_class($this)) . '.phtml':
            Application::$applicationName . '/template/' . $this->templateFile;

        if (is_file($templateFile)) {
            include $templateFile;
        }

        $hasDisplay = true;
    }

    function displayTemplate($template) {
        $templateFile = Application::$applicationName . '/template/' . $template;
        if (is_file($templateFile)) {
            include $templateFile;
        }
#trace
        else {
            Trace::appendError(['errstr' => 'template file ' . $templateFile . ' not exists']);
        }
#endtrace
    }

    function __toString()
    {
        ob_start();
        $this->display();
        return ob_get_clean();
    }
}
