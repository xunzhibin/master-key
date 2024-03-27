<?php

// 命名空间
namespace Xzb\MasterKey;

use Xzb\MasterKey\Frameworks\CodeIgniter3\Controller AS CI3Controller;

if (defined('CI_VERSION')) {
	class BaseController extends CI3Controller {}
}

/**
 * 基础 控制器类
 */
abstract class Controller extends BaseController
{
	/**
	 * 业务类
	 * 
	 * @var \Xzb\MasterKey\Service
	 */
	protected $service;

}
