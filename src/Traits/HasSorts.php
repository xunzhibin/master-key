<?php

// 命名空间
namespace Xzb\MasterKey\Traits;

// 排序 异常类
use Xzb\MasterKey\Exceptions\NotSetException;
use Xzb\MasterKey\Exceptions\ArgumentCountException;

/**
 * 排序
 * 
 */
trait HasSorts
{
	/**
	 * 排序
	 * 
	 * 	单个 全表排序
	 * 		[ 'column' => [] ]
	 * 	单个 条件排序
	 * 		[ 'column' => [ '条件1', ... ] ]
	 * 	多个
	 * 		[
	 * 			'column1' => [],
	 * 			'column2' => [ '条件1', ... ],
	 * 		]
	 * 
	 * @var array
	 */
	protected $sorts = [];

	/**
	 * 插入 是否添加排序
	 * 
	 * @var bool
	 */
	protected $insertSorting = false;

	/**
	 * 排序缓存集合
	 * 
	 * @var array
	 */
	protected static $sortCaches = [];

	// /**
	//  * 查询 排序
	//  * 
	//  * @var string
	//  */
	// protected $querySort = '-id';

	/**
	 * 获取 排序
	 * 
	 * @return array
	 */
	public function getSorts()
	{
		return $this->sorts;
	}

	/**
	 * 设置 排序
	 * 
	 * @param string $key
	 * @param array $where
	 * @return $this
	 */
	public function setSort(string $key, array $where)
	{
		$this->sorts[$key] = $where;

		return $this;
	}

	/**
	 * 设置 原始 排序
	 * 
	 * @param array $sorts
	 * @return $this
	 */
	public function setRawSorts(array $sorts)
	{
		$this->sorts = $sorts;

		return $this;
	}

	/**
	 * 重置 排序
	 * 
	 * @return $this
	 */
	public function resetSorts()
	{
		$this->sorts = [];

		return $this;
	}

	/**
	 * 启用 插入排序
	 * 
	 * @return $this
	 */
	public function enableInsertSort()
	{
		$this->insertSorting = true;

		return $this;
	}

	/**
	 * 禁用 插入排序
	 * 
	 * @return $this
	 */
	public function disableInsertSort()
	{
		$this->insertSorting = false;

		return $this;
	}

	// /**
	//  * 设置 查询排序
	//  * 
	//  * @param string $sort
	//  * @return $this
	//  */
	// public function setQuerySort(string $sort)
	// {
	// 	$this->querySort = $sort;

	// 	return $this;
	// }

	// /**
	//  * 获取 查询排序
	//  * 
	//  * @return string
	//  */
	// public function getQuerySort()
	// {
	// 	return $this->querySort;
	// }

	/**
	 * 添加 插入 排序
	 * 
	 * @param array $data
	 * @return array
	 */
	public function addInsertSort($data)
	{
		if ($sorts = $this->getSorts()) {
			// 数据 转为 二维数组
			$values = is_array(reset($data)) ? $data : [$data];

			// 循环 数据行
			foreach ($values as &$value) {
				// 循环 排序列
				foreach ($sorts as $columnName => $filterColumnNames) {
					$value = $this->addSort($value, $columnName, $filterColumnNames);
				}
			}

			return is_array(reset($data)) ? $values : reset($values);
		}

		return $datas;
	}

	/**
	 * 添加 排序
	 * 
	 * @param array $data
	 * @param string $columnName
	 * @param array $filterColumnNames
	 * @return array
	 */
	public function addSort(array $data, string $columnName, array $filterColumnNames = [])
	{
		// 排序 筛选器
		$filter = $filterColumnNames
						? array_intersect_key($data, array_fill_keys($filterColumnNames, null))
						: [];

		// 缺少 筛选数据
		if (count($filter) != count($filterColumnNames)) {
			throw new ArgumentCountException(
				get_class($this) . ' sort column [' . $columnName . '] missing query parameter [' . implode('-', array_diff($filterColumnNames, array_keys($filter))) . ']'
			);
		}

		$data[$columnName] = $this->getSortNumber($columnName, $filter);

		return $data;
	}

	/**
	 * 获取 排序编号
	 * 
	 * @param string $columnName
	 * @param array $filter
	 * @return int
	 */
	public function getSortNumber(string $columnName, array $filter = [])
	{
		// 筛选数据 格式化
		ksort($filter);
		$cacheKey = md5(serialize($filter));

		// 当前 最大排序编号
		$currentMaxSort = isset(static::$sortCaches[$cacheKey])
						? static::$sortCaches[$cacheKey]
						: $this->max($columnName, $filter);

		// 自增 +1
		// 缓存 排序编号 预防批量写入
		return static::$sortCaches[$cacheKey] = ++$currentMaxSort;
	}

}
