<?php

// 命名空间
namespace Xzb\CodeIgniter3\Resources;

// JSON 序列化接口
use JsonSerializable;

/**
 * Json 资源
 */
class JsonResource implements JsonSerializable
{
	/**
	 * 资源 实例
	 * 
	 * @var mixed
	 */
	public $resource;

	/**
	 * 构造函数
	 * 
	 * @param mixed $resource
	 * @return void
	 */
	public function __construct($resource)
	{
		$this->resource = $resource;
	}

	/**
	 * 资源 转为 数组
	 * 
	 * @return array
	 */
	public function toArray()
	{
		if (is_null($this->resource)) {
			return [];
		}

		return is_array($this->resource)
				? $this->resource
				: $this->resource->toArray();
	}

	/**
	 * 创建 HTTP响应
	 * 
	 * @return object
	 */
	public function toResponse()
	{
		$data = $this->toArray();
		if (! array_key_exists($wrapper = 'data', $data)) {
			$data = [ $wrapper => $data];
		}

		return array_merge(
			$data
			,$this->getMeta()
		);

		// return [
		// 	'data' => $this->toArray()
		// ];
	}

	/**
	 * 获取 元数据
	 * 
	 * @return array
	 */
	protected function getMeta()
	{
		return [];
	}

	/**
	 * 资源 解析为 数组
	 * 
	 * @return array
	 */
	public function resolve()
	{
		$data = $this->toArray();

		return $data;
	}

	/**
	 * JSON 序列化
	 * 
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->resolve();
	}

// ---------------------- PHP 魔术方法(属性重载) ----------------------
	/**
	 * 动态 获取 属性
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
        return $this->resource->{$key};
	}



	///////////////////////////////////////////////////////////////
	// /**
	//  * 资源 解析为 数组
	//  * 
	//  * @param array
	//  */
	// public function resolue()
	// {
	// 	$data = $this->toArray();

	// 	return $this->filter((array) $data);
	// }

	// /**
	//  * 过滤 数据
	//  * 
	//  * @param array $data
	//  * @return array
	//  */
	// protected function filter(array $data)
	// {
	// 	$index = -1;
	// 	foreach ($data as $key => $value) {
	// 		// 销毁 空类
	// 		if ($value instanceof stdclass) {
	// 			unset($data[$key]);
	// 			continue;
	// 		}

	// 		// 合并数据
	// 		if (is_numeric($key)) {

	// 					//             return $this->mergeData(
	// 	//                 $data, $index, $this->filter($value->data),
	// 	//                 array_values($value->data) === $value->data
	// 	//             );
	// 	        // return $this->removeMissingValues(array_slice($data, 0, $index, true) +
    //             // $merge +
    //             // $this->filter(array_slice($data, $index + 1, null, true)));
	// 		}
	// 	}

	// 	return $data;

	// 	//     var_dump(__FILE__ . ' ' . __FUNCTION__);
	// 	//     $index = -1;

	// 	//     foreach ($data as $key => $value) {
	// 	//         $index++;

	// 	//         if (is_array($value)) {
	// 	//             $data[$key] = $this->filter($value);

	// 	//             continue;
	// 	//         }

	// 	//         if (is_numeric($key) && $value instanceof MergeValue) {
	// 	//             return $this->mergeData(
	// 	//                 $data, $index, $this->filter($value->data),
	// 	//                 array_values($value->data) === $value->data
	// 	//             );
	// 	//         }

	// 	//         if ($value instanceof self && is_null($value->resource)) {
	// 	//             $data[$key] = null;
	// 	//         }
	// 	//     }

	// 	// 删除 缺失值
	// 	// return $this->removeMissingValues($data);
	// }

	// /**
	//  * 删除 缺失值
	//  * 
	//  * @param array $data
	//  * @return array
	//  */
	// protected function removeMissingValues(array $data)
	// {
	// 	foreach ($data as $key => $value) {
	// 		if ($value instanceof stdclass) {
	// 			unset($data[$key]);
	// 		}
	// 	}

	// 	return $data;
	// 	//     var_dump(__FILE__ . ' ' . __FUNCTION__);
	// 	//     $numericKeys = true;

	// 	//     foreach ($data as $key => $value) {
	// 	//         if (($value instanceof PotentiallyMissing && $value->isMissing()) ||
	// 	//             ($value instanceof self &&
	// 	//             $value->resource instanceof PotentiallyMissing &&
	// 	//             $value->isMissing())) {
	// 	//             unset($data[$key]);
	// 	//         } else {
	// 	//             $numericKeys = $numericKeys && is_numeric($key);
	// 	//         }
	// 	//     }

	// 	//     if (property_exists($this, 'preserveKeys') && $this->preserveKeys === true) {
	// 	//         return $data;
	// 	//     }

	// 	//     return $numericKeys ? array_values($data) : $data;
	// }

	//    /**
    //  * Merge the given data in at the given index.
    //  *
    //  * @param  array  $data
    //  * @param  int  $index
    //  * @param  array  $merge
    //  * @param  bool  $numericKeys
    //  * @return array
    //  */
    // protected function mergeData($data, $index, $merge, $numericKeys)
    // {
    //     if ($numericKeys) {
    //         return $this->removeMissingValues(array_merge(
    //             array_merge(array_slice($data, 0, $index, true), $merge),
    //             $this->filter(array_values(array_slice($data, $index + 1, null, true)))
    //         ));
    //     }

    //     return $this->removeMissingValues(array_slice($data, 0, $index, true) +
    //             $merge +
    //             $this->filter(array_slice($data, $index + 1, null, true)));
    // }

	// /**
	//  * 条件成立时, 返回值
	//  * 
	//  * @param bool $condition
	//  * @param mixed $value
	//  * @param mixed $default
	//  * @return mixed
	//  */
	// protected function when($condition, $value, $default = null)
	// {
	// 	// 匿名函数
	// 	if (is_callable($condition)) {
	// 		$condition = $condition();
	// 	}

	// 	if ($condition) {
	// 		return $value;
	// 	}

	// 	return func_num_args() === 3 ? $default : new \stdClass();
	// }

	// /**
	//  * 条件成立时, 合并值
	//  * 
	//  * @param bool $condition
	//  * @param mixed $value
	//  * @return mixed
	//  */
	// protected function mergeWhen($condition, $value)
	// {
	// 	// 匿名函数
	// 	if (is_callable($condition)) {
	// 		$condition = $condition();
	// 	}

	// 	return $condition ? $value : new \stdClass();
	// }

	//     /**
    //  * Merge a value if the given condition is truthy.
    //  *
    //  * @param  bool  $condition
    //  * @param  mixed  $value
    //  * @return \Illuminate\Http\Resources\MergeValue|mixed
    //  */
    // protected function mergeWhen($condition, $value)
    // {
    //     return $condition ? new MergeValue(value($value)) : new MissingValue;
    // }


	/////////////////////////////////////

	// /**
	//  * 创建 资源集合
	//  * 
	//  * @param mixed $collection
	//  * @return
	//  */
	// public static function collection($collection)
	// {
	// 	return $collection->map(function ($value) {
	// 		return (new static($value))->toArray();
	// 	})->all();
	// }

	// /**
	//  * 条件成立时 合并值
	//  * 
	//  * @param bool $condition
	//  * @param mixed $value
	//  * @return mixed
	//  */
	// protected function mergeWhen($condition, $value)
	// {
	// 	if (is_callable($condition)) {
	// 		$condition = $condition();
	// 	}

	// 	return $condition ? $value : [];

	// 	// return $condition ? new MergeValue(value($value)) : new MissingValue;
	// 	//         if ($data instanceof Collection) {
    //     //     $this->data = $data->all();
    //     // } elseif ($data instanceof JsonSerializable) {
    //     //     $this->data = $data->jsonSerialize();
    //     // } else {
    //     //     $this->data = $data;
    //     // }
	// }



}
