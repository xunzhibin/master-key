<?php

// 命名空间
namespace Xzb\CodeIgniter3\Support;

// 第三方 字符串库 https://www.doctrine-project.org/projects/inflector.html
use Doctrine\Inflector\InflectorFactory;

/**
 * 字符串
 * 
 */
class Str
{
	/**
	 * 蛇形命名 缓存
	 *
	 * @var array
	 */
	protected static $snakeCache = [];

	/**
	 * 大驼峰命名 缓存
	 * 
	 * @var array
	 */
	protected static $bigCamelCache = [];

	/**
	 * 小驼峰命名 缓存
	 * 
	 * @var array
	 */
	protected static $smallCamelCache = [];

// ---------------------- 转换 ----------------------
	/**
	 * 转为 复数形式
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function plural(string $value)
	{
		// 创建 新实例
		return InflectorFactory::create()->build()
								// 转 复数形式
								->pluralize($value);
	}

	/**
	 * 转为 单数形式
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function singular(string $value)
	{
		// 创建 新实例
		return InflectorFactory::create()->build()
								// 转 单数形式
								->singularize($value);
	}

	/**
	 * 转为 蛇形命名法
	 *
	 * @param  string  $value
	 * @param  string  $delimiter
	 * @return string
	 */
	public static function snake(string $value, string $delimiter = '_')
	{
		$key = $value;

		// 已缓存
		if (isset(static::$snakeCache[$key][$delimiter])) {
			return static::$snakeCache[$key][$delimiter];
		}

		// 创建 新实例
		$value = InflectorFactory::create()->build()
								// 转 蛇形命名 字符串
								->tableize($value);

		// 检测 替换 分隔符
		if ($delimiter != $defaultDelimiter = '_') {
			$value = str_replace($defaultDelimiter, $delimiter, $value);
		}

		return static::$snakeCache[$key][$delimiter] = $value;
	}

	/**
	 * 转为 大驼峰命名法
	 * 
	 * 首字母大写
	 * 
	 * @param string $value
	 * @return string
	 */
	public static function bigCamel(string $value)
	{
		$key = $value;

		// 已缓存
		if (isset(static::$bigCamelCache[$key])) {
			return static::$bigCamelCache[$key];
		}

		// 创建 新实例
		$value = InflectorFactory::create()->build()
								// 转 大驼峰命名法
								->classify($value);

		// 缓存 并 返回
		return static::$bigCamelCache[$key] = $value;
	}

	/**
	 * 转为 小驼峰命名法
	 * 
	 * 首字母小写
	 * 
	 * @param string $value
	 * @return string
	 */
	public static function smallCamel(string $value)
	{
	    $key = $value;

		// 已缓存
	    if (isset(static::$smallCamelCache[$key])) {
	        return static::$smallCamelCache[$key];
	    }

	    // 创建 新实例
	    $value = InflectorFactory::create()->build()
	                            // 转 小驼峰命名法
	                            ->camelize($value);

		// 缓存 并 返回
	    return static::$smallCamelCache[$key] = $value;
	}

}
