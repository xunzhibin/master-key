<?php
namespace Xzb\CodeIgniter3\Core;

/**
 * 输入类
 */
class Input extends \CI_Input
{
	/**
	 * 获取 指定子集
	 * 
	 * @param mixed $keys
	 * @return array
	 */
	public function only($keys, $xss_clean = null)
	{
		switch (strtolower($this->method())) {
			case 'get':
				return $this->get($keys, $xss_clean);
			case 'post':
				return $this->post($keys, $xss_clean);
			case 'put':
			case 'patch':
			case 'delete':
				return $this->input_stream($keys, $xss_clean);
		}
	}

}
