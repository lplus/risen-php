<?php #release
/**
 * Created by PhpStorm.
 * User: riki
 * Date: 15/5/27
 * Time: 上午9:26
 */

namespace risen;


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
