<?php

// 命名空间
namespace Xzb\MasterKey\Traits;

/**
 * 关键字
 * 
 */
trait HasKeywords
{
	/**
	 * 模糊匹配
	 * 
	 * 	单列
	 * 		[ 'column' ]
	 * 	多列
	 * 		[ 'column1', ... ]
	 * 
	 * @var array
	 */
	protected $likes = [];

    /**
     * 获取 模糊匹配
     * 
     * @return array
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * 设置 模糊匹配
     * 
     * @param string $column
     * @return $this
     */
    public function setLike(string $column)
    {
		array_push($this->likes, $column);

        return $this;
    }

	/**
	 * 设置 原始 模糊匹配
	 * 
	 * @param array $likes
	 * @return $this
	 */
	public function setRawLikes(array $likes)
	{
		$this->likes = $likes;

		return $this;
	}

	/**
	 * 重置 模糊匹配
	 * 
	 * @return $this
	 */
	public function resetLikes()
	{
		$this->likes = [];

		return $this;
	}

}
