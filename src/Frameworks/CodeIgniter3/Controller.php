<?php

// 命名空间
namespace Xzb\MasterKey\Frameworks\CodeIgniter3;

/**
 * 控制器类
 */
abstract class Controller extends \CI_Controller
{
// ------------------------- 创建 -------------------------
	/**
	 * 创建(C)
	 */
	public function store()
	{
		$param = $this->input->post();

		// 创建
		$type = $this->service->create($param, true);

		$this->resource($type);
	}

// ------------------------- 查询 -------------------------
	/**
	 * 展示(R)
	 */
	public function show($id)
	{
		// 按 键值 查询 唯一记录
		$type = $this->service->soleByPrimaryKey($id);

		$this->resource($type);
	}

	/**
	 * 列表(R)
	 */
	public function index()
	{
		$params = $this->input->get([
			'filter',
			'keyword',
			'sort',
			'per_page',
			'fields'
		]);
		$params['filter'] = $params['filter'] ? (array)$params['filter'] : [];

		// 偏移量 分页
		$types = $this->service->offsetPaginate(
			$keyvalue = $params['filter'],
			$keyword = $params['keyword'],
			$sort = $params['sort'],
			$perPage = $params['per_page'],
			$columns = $params['fields'] ?? ''
		);

		$this->collection($types);
	}

// ------------------------- 更新 -------------------------
	/**
	 * 更新(U)
	 */
	public function update($id)
	{
		$param = $this->input->input_stream();

		// 更新
		$type = $this->service->updateSoleByPrimaryKey($id, $param);

		$this->resource($type);
	}

// ------------------------- 删除 -------------------------
	/**
	 * 删除(D)
	 */
	public function destroy($id)
	{
		// 删除
		$rows = $this->service->destroy($id);

		$this->resource();
	}

	// /**
	//  * 重新映射
	//  * 
	//  * @param string $method
	//  * @param array $params
	//  */
	// public function _remap(string $method, array $params = [])
	// {
	// 	echo "---------------------- 重新映射 -----------------------\r";
	// 	var_dump($method, $params);

	// 	// if ($method == 'index') {
	// 	// 	switch ($this->input->method()) {
	// 	// 		case 'post':
	// 	// 			$method = 'store';
	// 	// 			break;
	// 	// 		case 'put':
	// 	// 		case 'PATCH':
	// 	// 			$method = 'update';
	// 	// 			break;
	// 	// 		case 'delete':
	// 	// 			$method = 'destroy';
	// 	// 		case 'get':
	// 	// 		default:
	// 	// 			if (array_key_exists('id', $this->input->get())) {
	// 	// 				$method = 'show';
	// 	// 			}
	// 	// 			break;
	// 	// 	}
	// 	// }

	// 	if (method_exists($this, $method)) {
	// 		return call_user_func_array([$this, $method], $params);
	// 	}

	// 	show_404();
	// }

	/**
	 * 响应 资源
	 *
	 * @param mixed $output
	 * @return void
	 */
	public function resource($output = [])
	{
		$output = [
			// HTTP响应状态码
			'code' => 200,
			// 状态 success(默认) fail(HTTP状态响应码: 500-599) error(HTTP状态响应码: 400-499) 
			'status' => 'success', 
			// 错误码 HTTP响应状态码 + 错误类型() + message编号
			'errcode' => 0,
			// 错误的具体描述 当状态为error和fail时有效
			'message' => '',
			// 响应body fail、error时包含错误原因或异常名称
			'data' => $output,

			// 服务器时间
			'server_timestamp'	=> $time = time(),
			'server_datetime'	=> date('Y-m-d H:i:s', $time),
			// 服务器语言
			'server_language'	=> config_item('language'),
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($output, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * 响应 资源集合
	 *
	 * @param mixed $output
	 * @return void
	 */
	public function collection($output)
	{
		$output = [
			// HTTP响应状态码
			'code' => 200,
			// 状态 success(默认) fail(HTTP状态响应码: 500-599) error(HTTP状态响应码: 400-499) 
			'status' => 'success', 
			// 错误码 HTTP响应状态码 + 错误类型() + message编号
			'errcode' => 0,
			// 错误的具体描述 当状态为error和fail时有效
			'message' => '',
			// 响应body fail、error时包含错误原因或异常名称
			'data' => (array)$output->data,
			// 响应元信息
			'meta' => [
				'total' => $output->total,
				'per_page' => $output->per_page,
				'current_page' => $output->current_page,
				'last_page' => $output->last_page,
				// 'keyword' => $output->keyword ?? ''
			],

			// 服务器时间
			'server_timestamp'	=> $time = time(),
			'server_datetime'	=> date('Y-m-d H:i:s', $time),
			// 服务器语言
			'server_language'	=> config_item('language'),
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($output, JSON_UNESCAPED_UNICODE));
	}

	// /**
	//  * 输出
	//  * 
	//  * @param mixed $output
	//  */
	// public function _output($output)
	// {
	// 	echo '<br>---------------------- 输出 -----------------------<pre>';

	// 	$responseBody = [
	// 		// HTTP响应状态码
	// 		'code',
	// 		// 状态 success(默认) fail(HTTP状态响应码: 500-599) error(HTTP状态响应码: 400-499) 
	// 		'status', 
	// 		// 错误码 HTTP响应状态码 + 错误类型() + message编号
	// 		'errcode',
	// 		// 错误的具体描述 当状态为error和fail时有效
	// 		'message',
	// 		// 响应body fail、error时包含错误原因或异常名称
	// 		'data',
	// 	];

	// 	echo json_encode([
	// 		// HTTP响应状态码
	// 		'code'		=> 200,
	// 		// 状态 success(默认) fail(HTTP状态响应码: 500-599) error(HTTP状态响应码: 400-499) 
	// 		'status'	=> 'success', 
	// 		// 错误码 HTTP响应状态码 + 错误类型() + message编号
	// 		'errcode'	=> 0,
	// 		// 错误的具体描述 当状态为error和fail时有效
	// 		'message'	=> '',
	// 		// 响应body fail、error时包含错误原因或异常名称
	// 		'data'		=> '失败、错误时设置未异常名称',

	// 		// 服务器时间
	// 		'server_timestamp'	=> $time = time(),
	// 		'server_datetime'	=> date('Y-m-d H:i:s', $time),
	// 		// 服务器语言
	// 		'server_language'	=> config_item('language'),
	// 	], JSON_UNESCAPED_UNICODE);
	// }

	// /**
	//  * 析构函数
	//  */
	// public function __destruct()
	// {
	// 	echo "\r---------------------- 析构函数 -----------------------\r";
	// 	$queries = [];
    //     $totalTime = 0;

    //     if (! empty($this->db->queries)) {
    //         foreach ($this->db->queries as $key => $sql) {
    //             $time = number_format($this->db->query_times[$key], 4);
    //             $totalTime += $time;

    //             $queries[] = [
    //                 // 'sql' => $sql,
    //                 'sql' => str_replace(["\n"], ' ', $sql),
    //                 // 'sql' => str_replace(["\n"], '', $sql),
    //                 'time' => $time . ' 秒'
    //             ];
    //         }
    //     }

	// 	var_dump([
    //         'total_time' => $totalTime,
    //         'queries' => $queries,
	// 	]);
	// }

}
