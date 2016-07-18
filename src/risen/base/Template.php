<?php
/**
 * Project: risenphp.
 * User: riki
 * Date: 16/7/17
 * Time: 上午10:47
 */

namespace risen\base;


use risen\Trace;

class Template
{
	function display()
	{
		$class = get_called_class();// app\template\XxTemplate
		$templateFile = str_replace("\\", "/" , $class) . '.phtml';
		if (is_file($templateFile)) {
			include $templateFile;
		}
#trace
		else {
			Trace::appendError(['errstr' => "template file $templateFile not exists"]);
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