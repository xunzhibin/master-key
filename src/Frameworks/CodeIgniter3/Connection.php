<?php

// 命名空间
namespace Xzb\MasterKey\Frameworks\CodeIgniter3;

/**
 * 连接
 * 
 */
class Connection
{
	/**
	 * 连接 数据
	 * 
	 * @param string $group
	 * @return \CI_DB_query_builder
	 */
	public function query(string $group = '')
	{
		// 未连接
		if(! isset(get_instance()->db) || ! get_instance()->db) {
			// 连接数据库
			// get_instance()->load->database();
			$this->database($group);
		}

		return get_instance()->db;
	}

	/**
	 * 获取 路径集合
	 * 
	 * @return array
	 */
	public function getPaths()
	{
		return $paths = get_instance()->load->get_package_paths();
	}

	/**
	 * 获取 数据库 配置
	 * 
	 * @param string $group
	 * @return array
	 */
	public function getDatabaseConfig(string $group = '')
	{
		$filePath = '';

		// 循环
		foreach ($this->getPaths() as $path) {
			if (file_exists($envPath = $path . 'config/' . ENVIRONMENT . '/database.php')) {
				$filePath = $envPath;
				break;
			}
			else if (file_exists($path = $path.'config/database.php')) {
				$filePath = $path;
				break;
			}
		}
		if (! $filePath) {
			show_error('The configuration file database.php does not exist.');
		}

		// 加载配置文件
		include($filePath);

		if (! isset($db) OR count($db) === 0) {
			show_error('No database connection settings were found in the database config file.');
		}

		if ($group !== '') {
			$active_group = $params;
		}

		if (! isset($active_group)) {
			show_error('You have not specified a database connection group via $active_group in your config/database.php file.');
		}
		elseif ( ! isset($db[$active_group])) {
			show_error('You have specified an invalid database connection group ('.$active_group.') in your config/database.php file.');
		}

		return $db[$active_group];
	}

	/**
	 * 连接 数据库
	 * 
	 * @param string $group
	 * @return $this
	 */
	public function database(string $group = '')
	{
		$params = $this->getDatabaseConfig($group);

		$driver = 'CI_DB_' . $params['dbdriver'] . '_driver';

		$DB = new $driver($params);

		// 检查子驱动程序
		if ( ! empty($DB->subdriver)) {
			$driver = 'CI_DB_' . $DB->dbdriver . '_' . $DB->subdriver . '_driver';
			$DB = new $driver($params);
		}

		$DB->initialize();

		get_instance()->db = '';

		get_instance()->db =& $DB;

		return $this;
	}

}
