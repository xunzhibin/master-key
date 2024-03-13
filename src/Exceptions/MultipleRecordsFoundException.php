<?php

// 命名空间
namespace Xzb\MasterKey\Exceptions;

/**
 * 找到 多个记录 异常
 */
class MultipleRecordsFoundException extends BaseException
{
	/**
	 * 构造函数
	 *
	 * @param int $count
	 * @param string $model
	 * @param int $code
	 * @param \Throwable|null $previous
	 * @return void
	 */
	public function __construct($count, $model = '', $code = 500, $previous = null)
	{
		$message = "$count records were found for model [{$model}]";

		parent::__construct($message, $code, $previous);
	}

}
