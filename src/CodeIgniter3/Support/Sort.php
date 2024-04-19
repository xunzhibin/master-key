<?php

// 命名空间
namespace Xzb\CodeIgniter3\Support;

/**
 * 排序
 */
class Sort
{
    /**
     * 排序分隔符
     *
     * @var string
     */
    protected $sortDelimiter = ',';

    /**
     * 算术 映射方向
     *
     * @var array
     */
    protected $arithmeticMappingDirections = [
        '+' => 'asc', // 升序
        '-' => 'desc', // 降序
    ];

    /**
     * 方向分隔符
     *
     * @var string
     */
    protected $directionDelimiter = '|';

    /**
     * 算术运算符 排序
     *
     * @param string $sort
	 * @param string $sortDelimiter
     * @return array
     */
    protected function arithmeticOperators(string $sort, string $sortDelimiter = null)
    {
		// 以 排序分隔符 分隔字符串, 循环处理每一个排序
		$order = array_map(function ($field) {
			// 循环  算术 映射方向
			foreach ($this->arithmeticMappingDirections as $mapKey => $direction) {
				// 比较两个字符串
				if (strncmp($field, $mapKey, strlen($mapKey)) === 0) {
					// 相等
					$column = current(array_filter(explode($mapKey, $field, 2)));

					return [
						$column => $direction
					];
				}
			}

			return false;
		}, explode($sortDelimiter ?: $this->sortDelimiter, $sort));
	
		// 二维数组 合并成 一维数组
		$order = array_reduce(array_filter($order), 'array_merge', array());

		return $order;
    }

    /**
     * 排序方向 排序
     *
     * @param string $sort
	 * @param string $sortDelimiter
     * @return array
     */
    protected function sortDirections(string $sort, string $sortDelimiter = null, string $directionDelimiter = null)
    {
		// 方向分隔符
		$directionDelimiter = $directionDelimiter ?: $this->directionDelimiter;

		// 以 排序分隔符 分隔字符串, 循环处理每一个排序
		$order = array_map(function ($field) use ($directionDelimiter) {
			// 检测 字段方向分隔符 是否存在
			if (mb_strpos($field, $directionDelimiter) === false) {
				return FALSE;
			}

			// 分隔 排序字段、方向
			list($column, $direction) = explode($directionDelimiter, $field);

			// 检测 排序方向
			if (! in_array($direction = strtolower($direction), $this->arithmeticMappingDirections)) {
				return FALSE;
			}

			return [
				$column => $direction
			];
		}, explode($sortDelimiter ?: $this->sortDelimiter, $sort));

		// 二维数组 合并成 一维数组
		$order = array_reduce(array_filter($order), 'array_merge', array());

		return $order;
    }

    /**
     * 解析 排序
     *
     * @param string $sort
	 * @param string $type
     * 				arithmeticOperators(算术运算符)
	 * 				sortDirections(排序方向)
	 * @param string $sortDelimiter
     * @return array
     */
    public static function decode(
		string $sort, string $type = 'arithmeticOperators',
		string $sortDelimiter = null, string $directionDelimiter = null
	)
    {
		// 参数 数组
		$parameters = func_get_args();

		// 销毁 数组中类型
		unset($parameters[1]);

		// 解析
		return (new static)->{$type}(...$parameters);
    }

}
