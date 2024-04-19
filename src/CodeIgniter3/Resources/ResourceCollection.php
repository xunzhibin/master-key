<?php

// 命名空间
namespace Xzb\CodeIgniter3\Resources;

// 分页器
use Xzb\CodeIgniter3\Database\Eloquent\Paginator;

/**
 * 资源 集合
 */
class ResourceCollection extends JsonResource
{
	/**
	 * 集合实例
	 * 
	 * @var object
	 */
	public $collection;

	/**
	 * 构造函数
	 * 
	 * @param mixed $resource
	 * @return void
	 */
	public function __construct($resource)
	{
		parent::__construct($resource);

		// 将 集合 映射到 单个资源
		$this->resource = $this->mapResource($resource);
	}

	/**
	 * 映射资源
	 * 
	 * @param mixed $resource
	 * @return mixed
	 */
	protected function mapResource($resource)
	{
		// 获取 资源类
		$resourceClass = $this->getResourceClass();

		// 分页
		if ($resource instanceof Paginator) {
			$this->collection = $resourceClass ? $resource->getCollection()->mapInto($resourceClass) : $resource->getCollection();

			return $resource->setCollection($this->collection);
		}

		// 其它
		$this->collection = $resourceClass ? $resource->mapInto($resourceClass) : $resource;

		return $this->collection;

	}

	/**
	 * 获取 资源类
	 * 
	 * @return string|null
	 */
	protected function getResourceClass()
	{
		// 当前类名
		$class = get_class($this);
		// 资源集合类 后缀
		$cellSuffix = 'Collection';

		if (
			substr(basename($class), -strlen($cellSuffix)) === $cellSuffix
			&& ($position = strrpos($class, $cellSuffix)) !== false
			// 资源类 存在
			&& class_exists($class = substr_replace($class, 'Resource', $position, strlen($cellSuffix)))
		) {
			return $class;
		}

		return ;
	}

	/**
	 * 获取 元数据
	 * 
	 * @return array
	 */
	protected function getMeta()
	{
		// 分页
		if ($this->resource instanceof Paginator) {
		   $paginated = $this->resource->toArray();
		   unset($paginated['data']);

			return [
				'meta' => $paginated
			];
		}

		return parent::getMeta();
	}

	/**
	 * 转为 数组
	 * 
	 * @return array
	 */
	public function toArray()
	{
		// var_dump($this->collection);exit;
		return $this->collection->map->toArray()->all();
	}

}
