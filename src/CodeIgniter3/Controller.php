<?php

// 命名空间
namespace Xzb\CodeIgniter3;

// 排序
use Xzb\CodeIgniter3\Support\Sort;

/**
 * 控制器类
 */
abstract class Controller extends \CI_Controller
{
	/**
	 * 业务类
	 * 
	 * @var \Xzb\MasterKey\Service
	 */
	protected $service;

	/**
	 * 请求参数
	 * 
	 * @var array
	 */
	protected $params = [];

	/**
	 * 创建(C)
	 */
	public function store()
	{
		// 创建
		return $this->service->create($this->params);
	}

	/**
	 * 更新(U)
	 */
	public function update($id)
	{
		// 其它条件
		$keyvalue = [];

		// 更新
		return $this->service->update($id, $this->params, $keyvalue);
	}

	/**
	 * 读取(R)
	 */
	public function read($id)
	{
		// 其它条件
		$keyvalue = [];
		// 查询列
		$selectColumns = [];

		// 读取
		return $this->service->find($id, $keyvalue, $selectColumns);
	}

	/**
	 * 列表(R)
	 */
	public function index()
	{
		// 响应内容格式
		$bodyFormat = $this->params['body_format'] ?? 'filter' ?: 'filter';
		// 筛选条件
		$keyvalue = [];
		// 检索关键字
		$keyword = (string)$this->params['keyword'];
		// 排序
		$sort = Sort::decode((string)$this->params['sort']);
		// 查询 列
		$selectColumns = [];
		// 当前页
		$page = (int)$this->params['page'];
		// 查询条数
		$perPage = (int)$this->params['per_page'];

		switch ($bodyFormat) {
			// 筛选列表
			case 'filter':
				return $this->service->filter(
					$keyvalue,
					$keyword,
					$sort,
					$selectColumns,
					$perPage ?: null
				);
			// 偏移分页
			case 'offsetPaginate':
			// 默认
			default:
				return $this->service->offsetPaginate(
					$keyvalue,
					$keyword,
					$sort,
					$page,
					$perPage,
					$selectColumns
				);
		}
	}

	/**
	 * 删除(D)
	 */
	public function destroy($id)
	{
		// 其它条件
		$keyvalue = [];

		// 删除
		$rows = $this->service->destroy($id, $keyvalue);

		return [
			'data' => null
		];
	}

	/**
	 * 重映射方法
	 * 
	 * @param string $method
	 * @param array $params
	 */
	public function _remap(string $method, array $params = [])
	{
		$submitParam = $this->getSubmitParam(
			$method
		);

		$this->params = $this->input->only($submitParam);

		// 调用方法 不存在
		if (! method_exists($this, $method)) {
			show_404();
		}

		try {
			$result = call_user_func_array([$this, $method], $params);

			if (is_object($result)) {
				$result = in_array(basename(get_class($result)), ['Collection', 'Paginator'])
							? $this->getResponseCollection($result)
							: $this->getResponseResource($result);
			}

			// 输出响应
			$this->output->res($result);
		} catch (\Throwable $e) {
			throw $e;
		}
	}

	// // 输出
	// public function _output($output)
	// {
	// 	var_dump($output);
	// }


	//////////////////////////////////////////////////////////////

	public function getSubmitParam(string $method)
	{
		// var_dump(
		// 	$this->router->fetch_directory(),
		// 	$this->router->fetch_class(),
		// 	$this->router->fetch_method(),
		// 	$this->uri->ruri_string(),
		// 	// $uri,
		// 	$method
		// );
		$params = [
			// 'api/v1/languages/store' => [
			'store' => [
				'title',
				'en_title',
				'locale_title',
				'code',
				'ci_package'
			],
			// 'api/v1/languages/index' => [
			'index' => [
				'body_format',
				'page',
				'per_page',
				'keyword',
				'sort',
			],
			// 'api/v1/languages/stat' => [
			'stat' => [
				'keyword',
			],
			'read' => [

			],
			'update' => [
				'title',
				// 'en_title',
				// 'locale_title',
				// 'code',
				// 'ci_package'
			]
		];

		return $params[$method] ?? [];

		// if (array_key_exists($uri, $params)) {
		// 	return $params[$uri];
		// }
		// else if (array_key_exists($uri = $uri . '/' . $method, $params)) {
		// 	return $params[$uri];
		// }

		// return [];

		// $param['is_delete'] = 0;
		// $param['is_disabled'] = 0;

		// return $params[$uri] ?? [];
	}

	// 创建 参数
	public function createParams()
	{
		// $paramTypes = [
		// 	'title'			=> 'string',
		// 	'en_title'		=> 'string',
		// 	'locale_title'	=> 'string',
		// 	'code'			=> 'string',
		// 	'ci_package'	=> 'string',
		// ];

		// $input = $this->input->only(array_keys($paramTypes));
		// $params = [];
		// foreach ($input as $key => $value) {
		// 	if (! is_null($value)) {
		// 		switch ($paramTypes[$key]) {
		// 			case 'string':
		// 				$value = (string)$value;
		// 				break;
		// 			case 'int':
		// 				$value = (int)$value;
		// 				break;
		// 		}
		// 		$params[$key] = $value;
		// 	}
		// }

		// var_dump($params);
	}
}
