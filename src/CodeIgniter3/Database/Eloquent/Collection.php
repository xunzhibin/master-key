<?php

// 命名空间
namespace Xzb\CodeIgniter3\Database\Eloquent;

use Xzb\CodeIgniter3\Support\HigherOrderCollectionProxy;

// // PHP 标准库(SPL) 接口
// use Countable;
// PHP 数组式访问
use ArrayAccess;
// JSON 序列化接口
use JsonSerializable;

/**
 * 集合
 */
// class Collection implements Countable
// class Collection
class Collection implements ArrayAccess, JsonSerializable
// class Collection implements ArrayAccess
{
	/**
	 * 包含项
	 * 
	 * @var array
	 */
	protected $items = [];

	/**
	 * 代理 方法
	 * 
	 * @var array
	 */
	protected static $proxies = [
        'map',
	];

	/**
	 * 构造函数
	 * 
	 * @param array $items
	 */
	public function __construct($items = [])
	{
		// $this->items = is_array($items) ? $items : (array)$items;
		$this->items = $this->getArrayableItems($items);
	}

	/**
	 * 获取 项目
	 * 
	 * @param mixed $items
	 * @return array
	 */
	protected function getArrayableItems($items)
	{
		// var_dump($items);
		// $self = get_class($this);

		if (is_array($items)) {
			return $items;
		}
		// else if ($items instanceof $self) {
		else {
			return $items->all();
		}

		return (array)$items;
	}

	// /**
	//  * 获取 集合实例
	//  * 
	//  * @return self
	//  */
	// public function toBase()
	// {
	// 	return new self($this);
	// }

	/**
	 * 项目 总数
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * 获取 第一个项目
	 * 
	 * @param mixed $default
	 * @return 
	 */
	public function first($default = null)
	{
		if ($this->count()) {
			return reset($this->items);
		}

		return $default;
	}

	/**
	 * 获取 所有项目
	 * 
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * 将值 映射到 新类中
	 * 
	 * @param $class
	 * @return static
	 */
	public function mapInto($class)
	{
		return $this->map(function ($value) use ($class) {
			return new $class($value);
		});

		// return $this->map(function ($value, $key) use ($class) {
		// 	return new $class($value, $key);
		// });

		// return $this->map(fn ($value, $key) => new $class($value, $key));
	}

	/**
	 * 每个项 应用回调函数
	 * 
	 * @param callable $callback
	 * @return static
	 */
	public function map(callable $callback)
	{
		$items = array_map($callback, $this->items);

		return new static($items);

		// $keys = array_keys($this->items);

		// $items = array_map($callback, $this->items, $keys);

		// $result = new static(array_combine($keys, $items));

		// return $result->toBase();
		// return $result;

		// return $result->contains(fn ($item) => ! $item instanceof Model) ? $result->toBase() : $result;

		// return $result->contains(function ($item) {
		// 	return ! $item instanceof Model;
		// }) ? $result->toBase() : $result;
	}

	// public function contains($key, $operator = null, $value = null)
	// {
	// 	if (! is_string($key) && is_callable($key)) {
	// 		$stdClass = new \stdClass;

	// 		$first = $stdClass;
	// 		foreach ($this->items as $k => $item) {
	// 			if ($key($item)) {
	// 				$first = $value;
	// 			}
	// 		}

	// 		return $first !== $stdClass;
	// 	}

	// 	return false;
	// }

	/**
	 * 转为 数组
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return $this->map(function ($value) {
			return is_object($value) ? $value->toArray() : $value;
		})->all();
	}

	/**
	 * 是否为 空
	 * 
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->items);
	}

// ---------------------- PHP ArrayAccess(数组式访问) 预定义接口 ----------------------
	/**
	 * 是否存在
	 * 
	 * @param mixed $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->items[$key]);
	}

	/**
	 * 获取
	 * 
	 * @param mixed $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->items[$key];
	}

	/**
	 * 设置
	 * 
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		if (is_null($key)) {
			$this->items[] = $value;
		}
		else {
			$this->items[$key] = $value;
		}
	}

	/**
	 * 销毁
	 * 
	 * @param $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->items[$key]);
	}

// ---------------------- PHP JsonSerializable(JSON序列化) 预定义接口 ----------------------
	/**
	 * JSON 序列化
	 * 
	 * @return array
	 */
	public function jsonSerialize()
	{
		return array_map(function ($item) {
			if ($item instanceof JsonSerializable) {
				return $item->jsonSerialize();
			}

			return $item;
		}, $this->all());
	}

// ---------------------- PHP 魔术方法 ----------------------
	/**
	 * 动态 访问 属性
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (! in_array($key, static::$proxies)) {
			throw new \Exception("Property [{$key}] does not exist on this collection instance.");
		}

		return new HigherOrderCollectionProxy($this, $key);
	}

}
