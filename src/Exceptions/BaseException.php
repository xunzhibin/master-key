<?php

// 命名空间
namespace Xzb\MasterKey\Exceptions;

// PHP 运行异常
use RuntimeException;

// 字符串 类
use Xzb\Support\Str;

/**
 * 基础 异常类
 */
class BaseException extends RuntimeException
{
	/**
	 * 类型
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * 键名
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * 标签
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * 参数
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * 构造函数
	 *
	 * @param string $model
	 * @param int $code
	 * @param \Throwable|null $previous
	 * @return void
	 */
	public function __construct(string $message = '', int $code = 500, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * 设置 类型
	 *
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * 获取 类型
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type ?: get_class($this);
	}

	/**
	 * 设置 键名
	 *
	 * @param string $key
	 * @return $this
	 */
	public function setKey(string $key)
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * 获取 键名
	 *
	 * @return string
	 */
	public function getKey()
	{
		return $this->key ?: str_replace('_exception', '', Str::snake(basename(static::class)));
	}

	/**
	 * 设置 标签
	 *
	 * @param string $label
	 * @return $this
	 */
	public function setLabel(string $label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * 设置 参数
	 *
	 * @param array $params
	 * @return $this
	 */
	public function setParams(array $params)
	{
		$this->params = $params;

		return $this;
	}

	/**
	 * 获取 参数
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

}
