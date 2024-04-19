<?php

// 命名空间
namespace Xzb\CodeIgniter3\Hooks;

/**
 * psr4 自动加载
 */
class Psr4Autoload
{
	/**
	 * 应用 命名空间 前缀
	 * 
	 * @var string
	 */
	const APP_PREFIX = 'App';

	/**
	 * 注册
	 * 
	 * @return void
	 */
	public static function register()
	{
		// 注册指定的函数作为 __autoload 的实现
		spl_autoload_register(function ($classname) {
			$filePath = strncmp($classname, $prefix = static::APP_PREFIX . '\\', strlen($prefix)) === 0
						? static::appClass($classname)
						: static::systemClass($classname);

			// 替换 分隔符
			$filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath);

			// 文件存在
			if (file_exists($filePath)) {
				// 加载
				require $filePath;
			}
		});
	}

	/**
	 * 应用程序类
	 * 
	 * @param string $classname
	 * @return string
	 */
	protected static function appClass(string $classname)
	{
		return APPPATH . ltrim($classname, static::APP_PREFIX . "\\") . '.php';
	}

	/**
	 * CI 系统类
	 * 
	 * @param string $classname
	 * @return string
	 */
	protected static function systemClass(string $classname)
	{
		switch ($classname) {
			case 'CI_DB_mysqli_driver';
				$fileName = ltrim($classname, $ciPrefix = 'CI_DB_');
				$driverName = mb_substr($fileName, 0, strpos($fileName, $needle = '_'));
				return BASEPATH . 'database/drivers/' . $driverName . DIRECTORY_SEPARATOR . $fileName . '.php';
			case 'CI_DB':
				return APPPATH . 'vendor/xzb/master-key/src/Frameworks/CodeIgniter3/Database/' . $classname . '.php';
			case 'CI_DB_query_builder':
			case 'CI_DB_driver':
				$fileName = ltrim($classname, $ciPrefix = 'CI_');
				return BASEPATH . 'database/' . $fileName . '.php';
			default:
				return ;
		}
	}

}
