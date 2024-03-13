<?php
/**
 * 不能使用命名空间 否则CI框架 系统代码 无法加载
 */
// namespace Xzb\MasterKey\Frameworks\CodeIgniter3;

class CI_DB extends \CI_DB_query_builder
{

// ---------------------- 重写方法 ----------------------
	/**
	 * 设置 操作表
	 * 
	 * 重新写方法 预防同一张表多次出现
	 *
	 * @param mixed $from
	 * @return $this
	 */
	public function from($from)
	{
		// 转为 数组
		if (! is_array($from)) {
			$from = explode(',', $from);
		}

		// 循环 设置
		foreach ((array) $from as $val) {
			$val = trim($val);

			$this->_track_aliases($val);

			$val = $this->protect_identifiers($val, TRUE, NULL, FALSE);

			if (! in_array($val, $this->qb_from)) {
				$this->qb_from[] = $val;

				if ($this->qb_caching === TRUE) {
					$this->qb_cache_from[] = $val;
					$this->qb_cache_exists[] = 'from';
				}
			}
		}

		return $this;
	}

	/**
	 * 批量插入
	 * 
	 * @param string $table
	 * @param array $set
	 * @param bool $escape
	 * @return int
	 */
	public function insert_batch($table = NULL, $set = NULL, $escape = NULL, $batch_size = 100)
	{
		return parent::insert_batch($table, $set, $escape, $batch_size);
	}

	/**
	 * 分页
	 * 
	 * @param int $page
	 * @param int $perPage
	 * @return $this
	 */
	public function page(int $page, int $perPage)
	{
		return $this->offset(($page - 1) * $perPage)->limit($perPage);
	}

// ---------------------- 扩展方法 ----------------------
	/**
	 * 错误信息
	 * 
	 * @return string
	 */
	public function message()
	{
		$error = $this->error();

		$message = '';
		if ($error['code']) {
			$message = 'Error(' . $error['code'] . '): ';
		}
		if ($error['message']) {
			$message .= $error['message'];
		}

		return $message;
	}

	/**
	 * 多条件
	 * 
	 * AND WHERE
	 * 
	 * @param array $where
	 * @param bool $escape
	 * @return $this
	 */
	public function wheres(array $wheres, $escape = null)
	{
		foreach ($wheres as $key => $value) {
			if (is_array($value)) {
				$this->where_in($key, $value, $escape);
			} else {
				$this->where($key, $value, $escape);
			}
		}

		return $this;
	}

	/**
	 * 排序 组
	 * 
	 * @param array $orderBy
	 * @param bool $escape
	 * @return $this
	 */
	public function orderByGroup(array $orderBy, $escape = null)
	{
		foreach ($orderBy as $column => $direction) {
			$this->order_by($column, $direction, $escape);
		}

		return $this;
	}

	/**
	 * 模糊匹配 组
	 * 
	 * @param array $columns
	 * @param string|null $keyword
	 * @param string $side
	 * @return $this
	 */
	public function likeGroup(array $columns, string $keyword = null, string $side = 'both', $escape = null)
	{
		if ($columns && $keyword) {
			// 条件组 开始
			$this->group_start();

			// 第一个 模糊匹配
			$this->like(array_shift($columns), $keyword, $side, $escape);

			foreach ($columns as $column) {
				$this->or_like($column, $keyword, $side, $escape);
			}

			// 条件组 结束
			$this->group_end();
		}

		return $this;
	}

}
