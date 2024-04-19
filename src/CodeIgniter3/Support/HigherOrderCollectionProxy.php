<?php

// 命名空间
namespace Xzb\CodeIgniter3\Support;

/**
 * 高阶集合代理
 */
class HigherOrderCollectionProxy
{
	/**
	 * 集合
	 * 
	 * @var object
	 */
	protected $collection;

	/**
	 * 方法
	 * 
	 * @var string
	 */
	protected $method;

	/**
	 * 构造函数
	 * 
	 * @param object $collection
	 * @param string $method
	 * @return void
	 */
	public function __construct($collection, $method)
	{
		$this->method = $method;
		$this->collection = $collection;
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
		return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
			return $value->{$method}(...$parameters);
		});
	}

	/**
	 * 
	 */


	//     /**
//      * Proxy a method call onto the collection items.
//      *
//      * @param  string  $method
//      * @param  array  $parameters
//      * @return mixed
//      */
//     public function __call($method, $parameters)
//     {
//         return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
//             return $value->{$method}(...$parameters);
//         });
//     }

}









// namespace Illuminate\Support;

// /**
//  * @mixin \Illuminate\Support\Enumerable
//  */
// class HigherOrderCollectionProxy
// {
//     /**
//      * Proxy accessing an attribute onto the collection items.
//      *
//      * @param  string  $key
//      * @return mixed
//      */
//     public function __get($key)
//     {
//         return $this->collection->{$this->method}(function ($value) use ($key) {
//             return is_array($value) ? $value[$key] : $value->{$key};
//         });
//     }

//     /**
//      * Proxy a method call onto the collection items.
//      *
//      * @param  string  $method
//      * @param  array  $parameters
//      * @return mixed
//      */
//     public function __call($method, $parameters)
//     {
//         return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
//             return $value->{$method}(...$parameters);
//         });
//     }
// }
