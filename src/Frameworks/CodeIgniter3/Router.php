<?php
namespace Xzb\MasterKey\Frameworks\CodeIgniter3;

use Xzb\Support\Str;

/**
 * 路由
 */
class Router extends \CI_Router
{
	/**
	 * 设置 路由
	 * 
	 * 重写 父类 方法
	 *
	 * @return	void
	 */
	protected function _set_routing()
	{
		// 获取 路由
		$route = array_merge($this->getModuleRoute(), $this->getCiRoute());

		isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
		isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
		unset($route['default_controller'], $route['translate_uri_dashes']);
		$this->routes = $route;

		if ($this->uri->uri_string !== '') {
			$this->_parse_routes();
		}
		else {
			$this->_set_default_controller();
		}
	}

	/**
	 * 获取 模块 路由
	 * 
	 * @return array
	 */
	protected function getModuleRoute()
	{
		list($client, $version, $moduleCode) = sscanf(implode('/', $this->uri->segments), '%[^/]/%[^/]/%[^/]');
		if (
			// 客户端 不在指定内
			! $client || ! in_array(strtolower($client), ['api'])
			// 版本号 格式不匹配
			|| ! $version || ! preg_match('#^v([1-9]+\d*)$#', $version)
			// 模块 不在指定内
			// || array_key_exists($moduleName, $enableModules)
 			|| ! $moduleCode
		) {
			return [];
		}

		// 转为 单数模式
		$moduleCode = Str::singular((string)$moduleCode);

		// 获取 启用 模块集合
		$enableModules = array_column($this->getEnableModules(), null, 'code');

		// 模块 启用
		if (array_key_exists($moduleCode, $enableModules)) {
			$module = $enableModules[$moduleCode];

			// 模块 路由 文件
			$moduleRouteFile = APPPATH . $module['root_dir'] . ucwords($moduleCode . '/routes/' . $client . '.php', '/');

			// 文件存在
			if (file_exists($moduleRouteFile)) {
				// 加载 模块 路由
				include($moduleRouteFile);
			}

			if (isset($route) && is_array($route)) {
				// 模块 控制器目录
				$moduleControllerDir = implode('/', [
					$moduleCode,
					'controllers',
					$version,
					'ci' . str_replace('.', '', CI_VERSION)
				]);
				$this->directory = '../' .  $module['root_dir']. ucwords($moduleControllerDir, '/') . '/';

				return $route;
			}
		}

		return [];
	}

	/**
	 * 获取 ci 框架 路由
	 * 
	 * @return array
	 */
	protected function getCiRoute()
	{
		if (file_exists(APPPATH.'config/routes.php')) {
			include(APPPATH.'config/routes.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/routes.php')) {
			include(APPPATH.'config/'.ENVIRONMENT.'/routes.php');
		}

		if (isset($route) && is_array($route)) {
			return $route;
		}

		return [];
	}

	/**
	 * 获取 模块 列表
	 * 
	 * @return array
	 */
	public function getModules()
	{
		// 加载 模块 配置文件
		$this->config->load('modules', false, true);
		
		return (array)$this->config->item('modules');

	}

	/**
	 * 获取 启动 模块
	 * 
	 * @return array
	 */
	public function getEnableModules()
	{
		return array_filter($this->getModules(), function ($value) {
			// 未禁用
			return ! $value['is_disabled'];
		});
	}

}
