<?php

// 命名空间
namespace Xzb\Exceptions;

/**
 * 不存在 异常类
 */
class NotExistException extends BaseException
{
	/**
	 * 构造函数
	 *
	 * @param string $model
	 * @param int $code
	 * @param \Throwable|null $previous
	 * @return void
	 */
	public function __construct(string $message = '', int $code = 404, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
