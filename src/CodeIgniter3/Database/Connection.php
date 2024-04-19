<?php

// 命名空间
namespace Xzb\CodeIgniter3\Database;

/**
 * 连接
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
	 * 连接 数据库
	 * 
	 * @param string $group
	 * @return $this
	 */
	protected function database(string $group = '')
	{
		// 获取 数据库 配置
		$params = $this->getDatabaseConfig($group);

		// 实例化 驱动器
		$driver = 'CI_DB_' . $params['dbdriver'] . '_driver';
		$DB = new $driver($params);

		// 检查 子驱动器
		if ( ! empty($DB->subdriver)) {
			// 实例化 子驱动器
			$driver = 'CI_DB_' . $DB->dbdriver . '_' . $DB->subdriver . '_driver';
			$DB = new $driver($params);
		}

		// 初始化数据库设置
		$DB->initialize();

		get_instance()->db = '';

		get_instance()->db =& $DB;

		return $this;
	}

	/**
	 * 获取 数据库 配置
	 * 
	 * @param string $group
	 * @return array
	 */
	protected function getDatabaseConfig(string $group = '')
	{
		if (
			! file_exists($filePath = APPPATH . 'config/' . ENVIRONMENT . '/database.php')
			&& ! file_exists($filePath = APPPATH . 'config/database.php')
		) {
			show_error('The configuration file database.php does not exist.');
		}

		// 加载配置文件
		include($filePath);

		if (! isset($db) OR count($db) === 0) {
			show_error('No database connection settings were found in the database config file.');
		}

		// 配置组
		if ($group !== '') {
			$active_group = $group;
		}

		if (! isset($active_group)) {
			show_error('You have not specified a database connection group via $active_group in your config/database.php file.');
		}
		elseif ( ! isset($db[$active_group])) {
			show_error('You have specified an invalid database connection group ('.$active_group.') in your config/database.php file.');
		}

		return $db[$active_group];
	}

}
