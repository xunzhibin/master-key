<?php

// 命名空间
namespace Xzb\MasterKey\Traits;

// 异常类
use Xzb\MasterKey\Exceptions\NotSetException;
use Xzb\MasterKey\Exceptions\AlreadyExistException;
use Xzb\MasterKey\Exceptions\ArgumentCountException;

/**
 * 唯一
 * 
 */
trait HasUniques
{
	/**
	 * 唯一
	 * 
	 * 	单个
	 * 		[ 'column1', ... ]
	 * 	多个
	 * 		[
	 * 			[ 'column' ],
	 * 			[ 'column1', ... ],
	 * 		]
	 * 
	 * @var array
	 */
	protected $uniques = [];

	/**
	 * 是否 启用 唯一写入验证
	 * 
	 * @var bool
	 */
	protected $uniqueWriteVerify = false;

	/**
	 * 唯一缓存集合
	 * 
	 * @var array
	 */
	protected static $uniqueCaches = [];

    /**
     * 获取 唯一
     * 
     * @return array
     */
    public function getUniques()
    {
        return $this->uniques;
    }

    /**
     * 设置 唯一
     * 
     * @param mixed $unique
     * @return $this
     */
    public function setUnique($unique)
    {
		$sortUnique = $unique;
		is_array($sortUnique) && sort($sortUnique);

		$sortUniques = $this->uniques; 
		array_walk($sortUniques, 'sort');

		if (! in_array($sortUnique, $sortUniques)) {
			array_push($this->uniques, $unique);
		}

        return $this;
    }

	/**
	 * 设置 原始 唯一
	 * 
	 * @param array $uniques
	 * @return $this
	 */
	public function setRawUniques(array $uniques)
	{
		$this->uniques = $uniques;

		return $this;
	}

	/**
	 * 重置 唯一
	 * 
	 * @return $this
	 */
	public function resetUniques()
	{
		$this->uniques = [];

		return $this;
	}

	/**
	 * 获取 唯一键名
	 * 
	 * @return array
	 */
	public function getUniqueKeyNames()
	{
		if ($uniqueKeyNames = $this->getUniques()) {
			// 唯一列 转为 二维数组
			if (count($uniqueKeyNames) == count($uniqueKeyNames, true)) {
				return [$uniqueKeyNames];
			}
		}

		return $uniqueKeyNames;
	}

	/**
	 * 启用 唯一写入验证
	 * 
	 * @return $this
	 */
	public function EnableUniqueWriteVerify()
	{
		$this->uniqueWriteVerify = true;

		return $this;
	}

	/**
	 * 禁用 唯一写入验证
	 * 
	 * @return $this
	 */
	public function disableUniqueWriteVerify()
	{
		$this->uniqueWriteVerify = false;

		return $this;
	}

	/**
	 * 验证 插入时 是否唯一
	 * 
	 * @param array $data
	 * @return bool
	 */
    public function verifyInsertUnique(array $data)
    {
		// 获取 唯一键名
		if ($uniqueKeyNames = $this->getUniqueKeyNames()) {
			// 数据 转为 二维数组
			$values = is_array(reset($data)) ? $data : [$data];

			// 循环 数据行
			foreach ($values as $value) {
				// 循环 唯一键
				foreach ($uniqueKeyNames as $keyName) {
					// 唯一键
					$uniqueKey = array_intersect_key($value, $keys = array_fill_keys($keyName, null));
					$uniqueKey = array_intersect_key(array_merge($keys, $uniqueKey), $uniqueKey);

					$this->verifyUnique($uniqueKey, $keyName);
				}
			}
		}

		return true;
    }

	/**
	 * 验证 更新时 是否唯一
	 * 
	 * @param array $changes
	 * @param array $original
	 * @return bool
	 */
    public function verifyUpdateUnique(array $changes, array $original)
	{
		// 获取 唯一键名
		if ($uniqueKeyNames = $this->getUniqueKeyNames()) {
			// 循环 唯一键
			foreach ($uniqueKeyNames as $keyName) {
				// 唯一键
				$uniqueKey = array_intersect_key($changes, $keys = array_fill_keys($keyName, null));
				// 未更新
				if (! $uniqueKey) {
					continue;
				}

				$uniqueKey = array_merge(array_intersect_key($original, $keys), $uniqueKey);

				$this->verifyUnique($uniqueKey, $keyName);
			}
		}

		return true;
	}

	/**
	 * 验证 唯一
	 * 
	 * @param array $uniqueKey
	 * @param array $uniqueKeyName
	 * @return bool
	 */
	protected function verifyUnique(array $uniqueKey, array $uniqueKeyName)
	{
		// 缺少参数
		if (count($uniqueKey) != count($uniqueKeyName)) {
			throw (new ArgumentCountException(
				get_class($this) . ' unique column [' . implode('-', $uniqueKeyName) . '] missing parameter [' . implode('-', array_diff($uniqueKeyName, array_keys($uniqueKey))) . ']'
			));
		}

		// 缓存值
		$uniqueKeyCacheValue = md5(serialize($uniqueKey));

		// 重复 或 已存在
		if ( in_array($uniqueKeyCacheValue, static::$uniqueCaches) || $this->exists($uniqueKey)) {
			throw (new AlreadyExistException(
				get_class($this) . ' unique column [' . implode('-', $uniqueKeyName) . '] data [' . implode('-', $uniqueKey) . '] already exists'
			))->setLabel(end($uniqueKeyName));
		}

		// 缓存 唯一 预防批量写入
		array_push(static::$uniqueCaches, $uniqueKeyCacheValue);

		return true;
	}

}
