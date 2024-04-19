<?php

// 命名空间
namespace Xzb\CodeIgniter3\Hooks;

/**
 * mime类型
 * 
 * 媒体类型（也通常称为多用途互联网邮件扩展或 MIME 类型）是一种标准，用来表示文档、文件或一组数据的性质和格式。
 */
class MimeType
{
	/**
	 * 解析
	 * 
	 * @return void
	 */
	public static function boot()
	{
		// 默认 类型
		$type = self::getDefaultType();

		if (
			// 是否 自动检测 响应内容类型
			config_item('use_request_mime_type')
			&& $requestType = self::getRequestType()
		) {
			$type = $requestType;
		}

		// 设置类型
		self::setType($type);
	}

	/**
	 * 获取 请求 mime类型
	 * 
	 * @return string
	 */
	protected static function getRequestType()
	{
		// 加载 输入类
		$input =& load_class('Input', 'core');

		$type = $input->server('HTTP_ACCEPT')
					?: $input->get_request_header('Accept')
					?: '';

		return strtolower($type);
	}

	/**
	 * 获取 默认 类型
	 * 
	 * @return string
	 */
	protected static function getDefaultType()
	{
		return $type = 'application/json';
	}

	/**
	 * 设置 mime类型
	 * 
	 * @param string $type
	 * @return void
	 */
	protected static function setType(string $type)
	{
		// 加载 输出类
		$output =& load_class('Output', 'core');

		// 设置 MIME 类型
		$output->set_content_type(strtolower($type));
	}

}
