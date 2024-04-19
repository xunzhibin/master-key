<?php
namespace Xzb\CodeIgniter3;

/**
 * 钩子
 */
class Hook
{
	/**
	 * 获取 钩子
	 * 
	 * @return array
	 */
	public static function getHooks()
	{
		$hooks = [];

		// 系统早期调用，只有 基准测试类 和 钩子类 被加载， 没有执行到路由或其他的流程
		$hooks['pre_system'][] = function () {
			self::preSystem();
		};

		// 控制器调用之前执行，所有的基础类都已加载，路由和安全检查已完成
		$hooks['pre_controller'][] = function () {
			self::preController();
		};

		// 控制器实例化之后立即执行，控制器的任何方法都还尚未调用
		// $hooks['post_controller_constructor'][] = function () {
		// 	self::postControllerConstructor();
		// };

		// 控制器完全运行结束时执行
		$hooks['post_controller'][] = function () {
			self::postController();
		};

		// 覆盖 _display() 方法，用于在系统执行结束时向浏览器发送最终结果
		// $hooks['display_override'][] = function () {
		// 	self::displayOverride();
		// };

		// 替代 输出类 中的 _display_cache() 方法
		// $hooks['cache_override'][] = function () {
		// 	self::cacheOverride();
		// };

		// 发送到浏览器之后、系统的最后期被调用
		// $hooks['post_system'][] = function () {
		// 	self::postSystem();
		// };

		return $hooks;
	}

	/**
	 * 系统前期 调用
	 * 只有 基准测试类 和 钩子类 被加载, 还没有执行到路由或其他的流程
	 * 
	 * @return void
	 */
	public static function preSystem()
	{
		// psr4 自动加载
		(new Hooks\Psr4Autoload)::register();
	}

	/**
	 * 控制器调用之前
	 * 所有的基础类都已加载，路由和安全检查也已经完成
	 * 
	 * @return void
	 */
	public static function preController()
	{
		// 语言
		(new Hooks\Language())::boot();

		// mime类型
		(new Hooks\MimeType())::boot();
	}

	/**
	 * 控制器实例化之后, 方法调用之前
	 * 
	 * @return void
	 */
	public static function postControllerConstructor()
	{
	}

	/**
	 * 控制器完全运行结束时执行
	 * 
	 * @return void
	 */
	public static function postController()
	{
	}

	/**
	 * 覆盖 _display() 方法
	 * 用于在系统执行结束时向浏览器发送最终结果
	 * 
	 * @return void
	 */
	public static function displayOverride()
	{
	}

	/**
	 * 替代 输出类 中的 _display_cache() 方法
	 * 
	 * @return void
	 */
	public static function cacheOverride()
	{
	}

	/**
	 * 发送到浏览器之后、系统的最后期被调用
	 * 
	 * @return void
	 */
	public static function postSystem()
	{
	}

}
