<?php #release
namespace risen;

/**
 * 处理类的自动加载,
 * 规则为将类全名(包括命名空间)的分隔符替换为文件系统路径分隔符,并加 .php 后缀
 * 
 * Class ClassLoader
 * @package risen
 */
class ClassLoader
{
    static function registerAutoload()
    {
        spl_autoload_register('risen\ClassLoader::loadClass');
    }

    private static $classPaths = array();

    static function additionalPath(array $paths)
    {
        self::$classPaths = $paths;
    }

    private static function loadClass($className)
    {
        $className = str_replace('\\', '/', $className);

        $classFile = dirname(__DIR__) . "/$className.php";
        if (is_file($classFile)) {
            include $classFile;
            return;
        }

        $classFile = $className . '.php';
        if (is_file($classFile)) {
            include $classFile;
            return;
        }

        foreach(self::$classPaths as $path)
        {
            if (is_file($file = $path . $className . '.php')) {
                include $file;
                return;
            }
        }
    }
}

ClassLoader::registerAutoload();
