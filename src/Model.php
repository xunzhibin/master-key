<?php

// 命名空间
namespace Xzb\MasterKey;

use Xzb\MasterKey\Frameworks\CodeIgniter3\Model AS CI3Model;

if (defined('CI_VERSION')) {
	class BaseModel extends CI3Model {}
}

/**
 * 基础 模型类
 */
abstract class Model extends BaseModel
{
}
