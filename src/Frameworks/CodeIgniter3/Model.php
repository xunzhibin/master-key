<?php

// 命名空间
namespace Xzb\MasterKey\Frameworks\CodeIgniter3;

use Xzb\Support\Str;
use Xzb\Support\Date;

/**
 * 模型类
 * 
 */
class Model
{
	use Traits\ForwardsCalls;

	/**
	 * 连接组
	 * 
	 * @var string
	 */
	protected $group = '';

	/**
	 * 关联 数据表
	 * 
	 * @var string
	 */
	protected $table;

	/**
	 * 主键 是否自增
	 * 
	 * 默认 自增
	 * 
	 * @var bool
	 */
	public $incrementing = true;

	/**
	 * 主键
	 * 
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * 主键 数据类型
	 * 
	 * @var string
	 */
	protected $primaryKeyType = 'int';

	/**
	 * 是否 使用 时间戳
	 * 
	 * 默认使用
	 * 
	 * @var bool
	 */
	public $timestamps = true;

	/**
	 * 日期列 存储格式
	 * 
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * 创建时间 列名
	 * 
	 * @var string|null
	 */
	const CREATED_AT = 'created_at';

	/**
	 * 更新时间 列名
	 * 
	 * @var string|null
	 */
	const UPDATED_AT = 'updated_at';

	/**
	 * 每页条数
	 * 
	 * @var int
	 */
	protected $perPage = 15;

// ---------------------- 数据库 ----------------------
	/**
	 * 获取 连接组
	 * 
	 * @return string
	 */
	public function getConnectionGroup()
	{
		return $this->group;
	}

	/**
	 * 设置 连接组
	 * 
	 * @param string $group
	 * @return $this
	 */
	public function setConnectionGroup(string $group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * 新建 查询构造器
	 * 
	 * @return Xzb\MasterKey\Frameworks\CodeIgniter3\Builder
	 */
	protected function query()
	{
		return (new Builder(
			(new Connection())->query($this->getConnectionGroup())
		))->setModel($this);
	}

// ---------------------- 数据表 ----------------------
	/**
	 * 获取 关联 数据表
	 * 
	 * @return string
	 */
	public function getTable()
	{
		return $this->table ?? Str::snake(Str::plural(
			basename(str_replace('\\', '/', get_class($this)))
		));
	}

	/**
	 * 设置 关联 数据表
	 * 
	 * @param string $table
	 * @return $this
	 */
	public function setTable(string $table)
	{
		$this->table = $table;

		return $this;
	}

// ---------------------- 主键 ----------------------
	/**
	 * 获取 主键 是否自增
	 * 
	 * @return bool
	 */
	public function getIncrementing()
	{
		return $this->incrementing;
	}

	/**
	 * 获取 主键名
	 * 
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return $this->primaryKey;
	}

	/**
	 * 设置 主键
	 * 
	 * @param string $key
	 * @return $this
	 */
	public function setPrimaryKeyName(string $key)
	{
		$this->primaryKey = $key;

		return $this;
	}

	/**
	 * 获取 主键 数据类型
	 * 
	 * @return string
	 */
	public function getPrimaryKeyType()
	{
		return $this->primaryKeyType;
	}

// ---------------------- 时间戳 ----------------------
	/**
	 * 是否使用 时间戳
	 * 
	 * @return bool
	 */
	public function usesTimestamps()
	{
		return $this->timestamps;
	}

	/**
	 * 获取 创建时间 列名
	 * 
	 * @return string|null
	 */
	public function getCreatedAtColumn()
	{
		return static::CREATED_AT;
	}

	/**
	 * 获取 更新时间 列名
	 * 
	 * @return string|null
	 */
	public function getUpdatedAtColumn()
	{
		return static::UPDATED_AT;
	}

	/**
	 * 获取 日期列 存储格式
	 * 
	 * @return string
	 */
	public function getDateFormat()
	{
		return $this->dateFormat ?: 'Y-m-d H:i:s';
	}

	/**
	 * 添加 时间 列
	 * 
	 * @param array $value
	 * @return array
	 */
	public function addTimestampsColumn(array $value)
	{
		// 数据 转为 二维数组
		$values = is_array(reset($value)) ? $value : [$value];

		// 转为 存储格式
		$time = $this->transformToStorageFormat($this->freshTimestamp());

		// 循环 添加
		foreach ($values as &$val) {
			// 创建时间
			if ($column = $this->getCreatedAtColumn()) {
				$val = array_merge([ $column => $time], $val);
			}

			// 更新时间
			if ($column = $this->getUpdatedAtColumn()) {
				$val = array_merge([ $column => $time], $val);
			}
		}

		$value = is_array(reset($value)) ? $values : reset($values);

		return $value;
	}

	/**
	 * 添加 更新时间 列
	 * 
	 * @param array $value
	 * @return array
	 */
	public function addUpdatedAtColumn(array $value)
	{
		if ($column = $this->getUpdatedAtColumn()) {
			// 数据 转为 二维数组
			$values = is_array(reset($value)) ? $value : [$value];

			// 转为 存储格式
			$time = $this->transformToStorageFormat($this->freshTimestamp());

			// 循环 添加 更新时间
			foreach ($values as &$val) {
				$val = array_merge([ $column => $time], $value);
			}

			return is_array(reset($value)) ? $values : reset($values);
		}

		return $value;
	}

	/**
	 * 新时间戳
	 * 
	 * @return \Xzb\Support\Date
	 */
	public function freshTimestamp()
	{
		return Date::now();
	}

// ---------------------- 转换 ----------------------
	/**
	 * 转为 存储格式
	 * 
	 * @param mixed $value
	 * @return string|null
	 */
	protected function transformToStorageFormat($value)
	{
		return empty($value)
				? $value
				: $this->transformToDateTimeObject($value)->format($this->getDateFormat());
	}

	/**
	 * 转为 DateTime 对象
	 * 
	 * @param mixed $value
	 * @return \Xzb\Support\Date
	 */
	protected function transformToDateTimeObject($value)
	{
		// 第三方库 Carbon实例
		if ($value instanceof CarbonInterface) {
			// 返回 Carbon实例
			return Date::instance($value);
		}

		// PHP库 DateTime实例
		if ($value instanceof DateTimeInterface) {
			// 按 日期格式、时区 解析 返回 Carbon实例
			return Date::parse(
				$value->format('Y-m-d H:i:s.u'), $value->getTimezone()
			);
		}

		// 数字或数字字符串
		if (is_numeric($value)) {
			// 以 UNIX时间戳 创建 Carbon实例
			return Date::createFromTimestamp($value);
		}

		// 标准 日期 格式
		if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
			// 返回 Carbon实例
			return Date::instance(
				// 根据 标准日期格式 解析 返回 日期时间格式 例如：2012-01-31 00:00:00
				Carbon::createFromFormat('Y-m-d', $value)->startOfDay()
			);
		}

		// 获取 日期列 存储格式
		$format = $this->getDateFormat();
		try {
			// 根据 存储格式 创建 Carbon实例
			$date = Date::createFromFormat($format, $value);
		} catch (InvalidArgumentException $e) {
			// 无效参数异常
			$date = false;
		}

		return $date ?: Date::parse($value);
	}

// ---------------------- 分页 ----------------------
	/**
	 * 获取 每页条数
	 * 
	 * @return int
	 */
	public function getPerPage()
	{
		return $this->perPage;
	}

	/**
	 * 设置 每页条数
	 * 
	 * @param int $perPage
	 * @return $this
	 */
	public function setPerPage(int $perPage)
	{
		$this->perPage = $perPage;

		return $this;
	}

	/**
	 * 解析 当前页码
	 * 
	 * @param string $pageName
	 * @param int $default
	 * @return int
	 */
	public static function resolveCurrentPage(string $pageName = 'page', int $default = 1)
	{
		$page = get_instance()->input->get($pageName);

		if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
			return (int) $page;
		}

		return $default;
	}

	/**
	 * 分页器
	 * 
	 * @param object $result
	 * @param int $total
	 * @param int $perPage
	 * @param int $currentPage
	 * @return object
	 */
	public static function paginator(object $results, int $total, int $perPage, int $currentPage)
	{
		return (object)[
			'total'			=> $total,
			'per_page'		=> $perPage,
			'current_page'	=> $currentPage,
			'last_page'		=> max((int) ceil($total / $perPage), 1),
			'data'			=> $results
		];
	}

// ---------------------- PHP 魔术方法 ----------------------
	/**
	 * 
	 * 处理调用 不可访问 成员方法
	 * 
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
        // 转发 调用方法
        return $this->forwardCallToObject($this->query(), $method, $parameters);
	}

	/**
	 * 处理调用 不可访问 静态方法 
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		// 静态方法 转为 动态方法
		return (new static)->$method(...$parameters);
	}

}
