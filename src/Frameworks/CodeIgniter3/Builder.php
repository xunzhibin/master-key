<?php

// 命名空间
namespace Xzb\MasterKey\Frameworks\CodeIgniter3;

// 异常类
use Xzb\MasterKey\Exceptions\ModelException;
use Xzb\MasterKey\Exceptions\RecordsNotFoundException;
use Xzb\MasterKey\Exceptions\MultipleRecordsFoundException;

/**
 * 构造器
 * 
 */
class Builder
{
	use Traits\ForwardsCalls;

	/**
	 * CI 查询构造器
	 * 
	 * @var \CI_DB_query_builder
	 */
	protected $query;

	/**
	 * 模型类
	 * 
	 * @var Xzb\MasterKey\Frameworks\CodeIgniter3\Model
	 */
	protected $model;

	/**
	 * 构造函数
	 * 
	 * @param object $query
	 * @var void
	 */
	public function __construct(\CI_DB_query_builder $query)
	{
		$this->query = $query;
	}

	/**
	 * 设置 模型类
	 * 
	 * @param Xzb\MasterKey\Frameworks\CodeIgniter3\Model $model
	 * @return $this
	 */
	public function setModel(Model $model)
	{
		$this->model = $model;

		// 设置 数据表
		$this->query->from($model->getTable());

		return $this;
	}

// ---------------------- 创建(C) ----------------------
	/**
	 * 创建
	 * 
	 * @param array $value
	 * @return array
	 */
	public function create(array $value)
	{
		$this->insert($value);

		// 自增主键
		if ($this->model->getIncrementing()) {
			$value[$this->model->getPrimaryKeyName()] = $this->insert_id();
		}

		// return $this->transformValue($value);
		return (object)$value;
	}

	/**
	 * 插入
	 * 
	 * @param array $values
	 * @return int
	 */
	public function insert(array $values)
	{
		if (! empty($values)) {
			// 转为 二维数组
			if (! is_array(reset($values))) {
				$values = [$values];
			}

			// 时间戳
			if ($this->model->usesTimestamps()) {
				$values = $this->model->addTimestampsColumn($values);
			}

			$rows = $this->set_insert_batch($values)->insert_batch();
			if (! $rows) {
				throw new ModelException(
					get_class($this->model) . ' --> ' . ($this->message() ?: 'insert failed')
				);
			}
		}

		return $rows;
	}

// ---------------------- 读取(R) ----------------------
	/**
	 * 总条数
	 * 
	 * @param bool $isResetSelect
	 * @return int
	 */
	public function count($isResetSelect = true)
	{
		return $this->count_all_results('', $isResetSelect);
	}

	/**
	 * 查询 记录
	 * 
	 * @param array|string $columns
	 * @return object
	 */
	public function get($columns = [])
	{
		// 执行查询
		$query = $this->select($columns)->get_where();
		if (! $query) {
			throw new ModelException(
				get_class($this->model) . ' --> ' . ($this->message() ?: 'select failed')
			);
		}

		// return $this->transformValue($query->result_array());
		return (object)$query->result();
	}

	/**
	 * 偏移量分页
	 * 
	 * @param int|null $perPage
	 * @param array $columns
	 * @param string $pageName
	 * @param int|null $page
	 * @return object
	 */
    public function paginate(int $perPage = null, $columns = [], string $pageName = 'page', int $page = null)
    {
		// 当前页
        $page = $page ?: $this->model::resolveCurrentPage($pageName);

		// 每页条数
        $perPage = $perPage ?: $this->model->getPerPage();

		$results = ($total = $this->count($isResetSelect = false))
						? $this->page($page, $perPage)->get($columns)
						: new \stdclass();

		return $this->model::paginator($results, $total, $perPage, $page);
    }

	/**
	 * 唯一 记录
	 *
	 * @param  array|string  $columns
	 * @return object
	 */
	public function sole($columns = [])
	{
		// 执行查询
		$result = $this->limit(2)->get($columns);

		// 记录条数
		$count = count(get_object_vars($result));

		// 不存在
		if ($count === 0) {
			throw new RecordsNotFoundException();
		}

		// 不唯一
		if ($count > 1) {
			throw (new MultipleRecordsFoundException(
				$count, get_class($this->model)
			));
		}

		return reset($result);
	}

	/**
	 * 第一个值
	 * 
	 * @param array|string $columns
	 * @return object
	 */
	public function first($columns = [])
	{
		// 执行查询
		$result = $this->limit(1)->get($columns);

		return reset($result);
	}

	/**
	 * 是否 存在
	 * 
	 * @return bool
	 */
	public function exists()
	{
		// 获取 已编译 SELECT 查询 SQL
		$select = $this->get_compiled_select();

		$sql = "SELECT EXISTS ({$select}) AS `exists`";

		// 查询
		if (! $query = $this->query($sql)) {
			throw new ModelException(
				get_class($this->model) . ' --> ' . ($this->message() ?: 'select failed')
			);
		}

		if ($result = $query->row_array()) {
			return (bool)$result['exists'];
		}

		return false;
	}

	/**
	 * 最大值
	 * 
	 * @param string $column
	 * @return mixed
	 */
	public function max(string $column)
	{
		$result = $this->select_max($column, $alias = $column . '_max')->first();

		return $result->$alias;
	}

// ---------------------- 更新(U) ----------------------
	/**
	 * 更新
	 * 
	 * @param array $value
	 * @return int
	 */
	public function update(array $value)
	{
		// 时间戳
		if ($this->model->usesTimestamps()) {
			$value = $this->model->addUpdatedAtColumn($value);
		}

		if (! $this->query->set($value)->update()) {
			throw new ModelException(
				get_class($this->model) . ' --> ' . ($this->message() ?: 'update failed')
			);
		}

		return $this->affected_rows();
	}

// ---------------------- 删除(D) ----------------------
	/**
	 * 强制删除
	 * 
	 * @return int
	 */
	public function forceDelete()
	{
		if (! $this->delete()) {
			throw new ModelException(
				get_class($this->model) . ' --> ' . ($this->message() ?: 'delete failed')
			);
		}

		return $this->affected_rows();
	}

	/**
	 * 删除
	 * 
	 * @return int
	 */
	public function destroy()
	{
		return $this->forceDelete();
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
		// 直通
		$this->passthru = [
			'insert_batch',
			'insert_id',
			'count_all_results',
			'get_where',
			'get_compiled_select',
			'query',
			'affected_rows',
			'delete',

			// 扩展
			'message',
		];
		if (in_array($method, $this->passthru)) {
			return $this->query->{$method}(...$parameters);
		}

		// 转发 调用方法
		$this->forwardCallToObject($this->query, $method, $parameters);

		return $this;
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
		var_dump(__FILE__ . ' --> ' . __FUNCTION__ . ' --> '. $method);exit;
	}
}
