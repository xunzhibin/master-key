<?php

// 命名空间
namespace Xzb\CodeIgniter3\Database\Eloquent;

use Xzb\CodeIgniter3\Support\Traits\ForwardsCalls;

/**
 * 分页器
 */
class Paginator
{
	use ForwardsCalls;

    /**
	 * 总数
     *
     * @var int
     */
    protected $total;

    /**
	 * 最后一页
     *
     * @var int
     */
    protected $lastPage;

    /**
	 * 分页 项
     *
     * @var \Xzb\CI3\Collection
     */
    protected $items;

    /**
	 * 每页条数
     *
     * @var int
     */
    protected $perPage;

    /**
	 * 当前页码
     *
     * @var int
     */
    protected $currentPage;

    /**
     * 构造函数
     *
     * @param  mixed  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int $currentPage
     * @return void
     */
    public function __construct($items, $total, $perPage, $currentPage)
    {
        $this->total		= $total;
        $this->perPage		= $perPage;
        $this->lastPage		= max((int) ceil($total / $perPage), 1);
        $this->currentPage	= $currentPage;
        $this->items		= $items;
    }

    /**
	 * 获取 当前页码
     *
     * @return int
     */
    public function currentPage()
    {
        return $this->currentPage;
    }

    /**
	 * 获取 每页条数
     *
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
	 * 获取 总数
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
	 * 获取 最后一页页码
     *
     * @return int
     */
    public function lastPage()
    {
        return $this->lastPage;
    }

    /**
	 * 转为 数组
     *
     * @return array
     */
    public function toArray()
    {
        return [
			'total'			=> $this->total(),
            'per_page'		=> $this->perPage(),
            'current_page'	=> $this->currentPage(),
			'last_page'		=> $this->lastPage(),
            'data'			=> $this->items->toArray(),
        ];
    }

	/**
	 * 获取 分页器 集合
	 * 
	 * @return object
	 */
	public function getCollection()
	{
		return $this->items;
	}

	/**
	 * 设置 分页器 集合
	 * 
	 * @param object
	 * @return $this
	 */
	public function setCollection($collection)
	{
		$this->items = $collection;

		return $this;
	}

	// /**
	//  * 对 集合 进行动态调用
	//  * 
	//  * @param string $method
	//  * @param array $parameters
	//  * @return mixed
	//  */
	// public function __call($method, $parameters)
	// {
	// 	return $this->forwardCallToObject($this->getCollection(), $method, $parameters);
	// }


	// 	var_dump($items);

    // /**
    //  * Make dynamic calls into the collection.
    //  *
    //  * @param  string  $method
    //  * @param  array  $parameters
    //  * @return mixed
    //  */
    // public function __call($method, $parameters)
    // {
    //     var_dump(__FILE__ . ' ' . __FUNCTION__ . ' ' . $method);
    //     return $this->forwardCallTo($this->getCollection(), $method, $parameters);
    // }
}
