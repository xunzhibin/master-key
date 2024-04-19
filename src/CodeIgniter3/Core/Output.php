<?php
namespace Xzb\CodeIgniter3\Core;

/**
 * 输出
 */
class Output extends \CI_Output
{
	public function res($response)
	{
		// if (is_object($response)) {
		// 	$response = $response->toArray();
		// }

		// if (! array_key_exists($key = 'data', $response)) {
		// 	$response = [
		// 		'data' => $response
		// 	];
		// }
		// var_dump(
		// 	$response,
        //     json_encode($response,0)
		// );exit;

		$output = array_merge([
			// HTTP响应状态码
			'code' => 200,
			// 状态 success(默认) fail(HTTP状态响应码: 500-599) error(HTTP状态响应码: 400-499) 
			'status' => 'success', 
			// 错误码 HTTP响应状态码 + 错误类型() + message编号
			'errcode' => 0,
			// 错误的具体描述 当状态为error和fail时有效
			'message' => '',
			// 响应body fail、error时包含错误原因或异常名称
			'data' => [],
		], $response);
		// var_dump($output);exit;

		$this->response($output);
	}

	/**
	 * 响应 资源
	 *
	 * @param array $output
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
		];

		$this->response($output);
	}

	/**
	 * 响应 资源集合
	 *
	 * @param array $output
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
			'data' => $output['data'],
			// 响应元信息
			'meta' => [
				'total' => $output['total'],
				'per_page' => $output['per_page'],
				'current_page' => $output['current_page'],
				'last_page' => $output['last_page'],
				// 'keyword' => $output->keyword ?? ''
			],
		];

		$this->response($output);
	}

	/**
	 * 响应
	 * 
	 * @param array $output
	 * @return void
	 */
	public function response(array $output)
	{
		$output = array_merge($output, [
			// 服务器时间
			'server_timestamp'	=> $time = time(),
			'server_datetime'	=> date('Y-m-d H:i:s', $time),
			// 服务器语言
			'server_language'	=> config_item('language'),
		]);

		if (defined('ENVIRONMENT') && in_array(ENVIRONMENT, ['development', 'testing'])) {
			$debugDatabase = [
				'total_time'	=> 0,
				'queries'		=> [],
			];

			$queries = get_instance()->db->queries ?? [];
			$queryTimes = get_instance()->db->query_times ?? [];
			if ($queries) {
				foreach ($queries as $key => $sql) {
					$time = number_format($queryTimes[$key], 4);

					$debugDatabase['total_time'] += $time;
					$debugDatabase['queries'][] = [
						'sql' => str_replace(["\n", "\r", "\r\n"], ' ', (string)$sql),
						'time' => $time . ' 秒'
					];
				}
			}

			$output = array_merge($output, [
				'debug_database' => $debugDatabase
			]);
		}

		// $this->set_content_type('application/json')
		$this->set_content_type($this->getMimeType())
			->set_output(json_encode($output, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * 设置 MIME 类型
	 * 
	 * @param string $type
	 * @return $this
	 */
	public function setMimeType(string $type)
	{
		$this->mime_type = $type;

		return $this;
	}

	/**
	 * 获取 MIME 类型
	 * 
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->mime_type;
	}

}
