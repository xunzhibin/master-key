<?php

// 命名空间
namespace Xzb\CodeIgniter3\Database\Eloquent;

use Xzb\CodeIgniter3\Support\Traits\ForwardsCalls;

// 异常类
use Xzb\Exceptions\ModelException;
use Xzb\Exceptions\RecordsNotFoundException;
use Xzb\Exceptions\MultipleRecordsFoundException;

/**
 * 构造器
 */
class Builder
{
	use ForwardsCalls;

	/**
	 * CI 查询构造器
	 * 
	 * @var \CI_DB_query_builder
	 */
	protected $query;

	/**
	 * 模型类
	 * 
	 * @var Xzb\MasterKey\Frameworks\CodeIgniter3\Model
	 */
	protected $model;

	/**
	 * 构造函数
	 * 
	 * @param object $query
	 * @var void
	 */
	public function __construct(\CI_DB_query_builder $query)
	{
		$this->query = $query;
	}

	/**
	 * 设置 模型类
	 * 
	 * @param Xzb\MasterKey\Frameworks\CodeIgniter3\Model $model
	 * @return $this
	 */
	public function setModel(Model $model)
	{
		$this->model = $model;

		// 设置 数据表
		$this->query->from($model->getTable());

		return $this;
	}

	/**
	 * 获取 模型类
	 * 
	 * @return Xzb\MasterKey\Frameworks\CodeIgniter3\Model
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * 获取 查询构造器
	 * 
	 * @return object
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * 设置 查询构造器
	 * 
	 * @param object $query
	 * @return $this
	 */
	public function setQuery($query)
	{
		$this->query = $query;

		return $this;
	}

    /**
	 * 创建 新模型实例
     *
     * @param  array  $attributes
	 * @return Xzb\MasterKey\Frameworks\CodeIgniter3\Model
     */
    public function newModelInstance(array $attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

	/**
	 * 获取 作用域 查询构造器
	 * 
	 * @return object
	 */
	public function toBase()
	{
		// return $this->applyScopes()->getQuery();

		return $this->getQuery();
	}

// ---------------------- 创建(C) ----------------------
	/**
	 * 创建
	 * 
	 * @param array $value
	 * @return Xzb\MasterKey\Frameworks\CodeIgniter3\Model
	 */
	public function create(array $attributes)
	{
		// 创建 新模型实例
		$this->model = $this->newModelInstance($attributes);

		// 保存
		$this->save();

		return $this->model;
	}

	/**
	 * 保存
	 * 
	 * @return bool
	 */
	public function save()
	{
		// 存在时, 更新
		if ($this->model->exists) {
			$saved = $this->model->isDirty() ?
                $this->performUpdate() : true;
		}
		// 不存在时, 插入
		else {
			// 执行 插入
			$saved = $this->performInsert();
		}

		// 同步 原始属性
        $this->model->syncOriginal();

		return $saved;
	}

	/**
	 * 执行 插入 操作
	 * 
	 * @return bool
	 */
	protected function performInsert()
	{
		// 使用 时间戳
		if ($this->model->usesTimestamps()) {
			// 添加 时间
			$this->model->updateTimestamps();
		}

		$sql = $this->query
			// 设置 插入数据
			->set($this->model->getInsertAttributes())
			// 获取 编译后 插入 SQL
			->get_compiled_insert();

		// 执行 SQL
		$query = $this->query->query($sql);
		if ($query === false) {
			throw new ModelException(
				get_class($this) . ' --> ' . ($this->errorMessage() ?: 'insert failed')
			);
		}

		// 设置 主键
		if ($id = $this->query->insert_id()) {
			$this->model->setAttribute($this->model->getPrimaryKeyName(), $id);
		}

		// 已存在
		$this->model->exists = true;

		return true;
	}

	/**
	 * 执行 更新 操作
	 * 
	 * @return bool
	 */
	protected function performUpdate()
	{
        // // If the updating event returns false, we will cancel the update operation so
        // // developers can hook Validation systems into their models and cancel this
        // // operation if the model does not pass validation. Otherwise, we update.
        // if ($this->fireModelEvent('updating') === false) {
        //     return false;
        // }

		// 使用 时间戳
        if ($this->model->usesTimestamps()) {
            $this->model->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->model->getDirty();
		// var_dump($dirty);exit;

        if (count($dirty) > 0) {
            // $this->setKeysForSaveQuery($query)->update($dirty);

			// 更新条件
			$this->wheres([
				$this->model->getPrimaryKeyName() => $this->model->getPrimaryKeyValueForUpdate()
			]);

			// 设置 更新数据
			$sql = $this->query->set($dirty)
				// 获取 编译后 更新 SQL
				->get_compiled_update();

			// 执行 SQL
			$query = $this->query->query($sql);
			if ($query === false) {
				throw new ModelException(
					get_class($this) . ' --> ' . ($this->errorMessage() ?: 'update failed')
				);
			}

            $this->model->syncChanges();
        }

        return true;
	}


// ---------------------- 读取(R) ----------------------
	/**
	 * 查询记录
	 * 
	 * @param array|string $columns
	 * @return
	 */
	public function get($columns = ['*'])
	{
		// $sql = $this->query
		// 			// 设置 查询 列
		// 			->select($columns)
		// 			// 获取 编译后 查询 SQL
		// 			->get_compiled_select();

		// 设置 查询 列
		$sql = $this->select($columns)
					// 获取 编译后 查询 SQL
					->get_compiled_select();

		// 执行 SQL
		$query = $this->query->query($sql);
		if ($query === false) {
			throw new ModelException(
				get_class($this) . ' --> ' . ($this->errorMessage() ?: 'select failed')
			);
		}

		$instance = $this->newModelInstance();

		return $instance->newCollection(array_map(function ($item) use ($instance) {
			return $instance->newExistInstance($item);
		}, $query->result_array()));


		//     $builder = $this->applyScopes();

		//     // If we actually found models we will also eager load any relationships that
		//     // have been specified as needing to be eager loaded, which will solve the
		//     // n+1 query issue for the developers to avoid running a lot of queries.
		//     if (count($models = $builder->getModels($columns)) > 0) {
		//         $models = $builder->eagerLoadRelations($models);
		//     }

		//     return $builder->getModel()->newCollection($models);
		// 
	}


	//     /**
    //  * Get the hydrated models without eager loading.
    //  *
    //  * @param  array|string  $columns
    //  * @return \Illuminate\Database\Eloquent\Model[]|static[]
    //  */
    // public function getModels($columns = ['*'])
    // {
    //     return $this->model->hydrate(
    //         $this->query->get($columns)->all()
    //     )->all();
    // }

	//     /**
    //  * Create a collection of models from plain arrays.
    //  *
    //  * @param  array  $items
    //  * @return \Illuminate\Database\Eloquent\Collection
    //  */
    // public function hydrate(array $items)
    // {
    //     $instance = $this->newModelInstance();

    //     return $instance->newCollection(array_map(function ($item) use ($items, $instance) {
    //         $model = $instance->newFromBuilder($item);

    //         if (count($items) > 1) {
    //             $model->preventsLazyLoading = Model::preventsLazyLoading();
    //         }

    //         return $model;
    //     }, $items));
    // }

	/**
	 * 查询 第一条 记录
	 * 
	 * @param array|string $columns
	 * @return object
	 */
	public function first($columns = ['*'])
	{
		return $this->limit(1)->get($columns)->first();
	}

	/**
	 * 总数
	 * 
	 * @param bool $isResetSelect
	 * @return int
	 */
	public function count(bool $isResetSelect = true)
	{
		return $this->count_all_results('', $isResetSelect);
		// $results = $this->get('COUNT(' . $column . ') AS aggregate');

		// if (! $results->isEmpty()) {
		// 	return $results->first()->aggregate;
		// }
	}

	/**
	 * 唯一 记录
	 * 
	 * @param array|string $columns
	 * @param object
	 */
	public function sole($columns = ['*'])
	{
		// 查询
		$result = $this->limit(2)->get($columns);

		// 总条数
		$count = $result->count();

		// 不存在
		if ($count === 0) {
			throw new RecordsNotFoundException;
		}

		// 不唯一
		if ($count > 1) {
			throw (new MultipleRecordsFoundException(
				$count, get_class($this->model)
			));
		}

		return $result->first();
	}

	/**
	 * 偏移量 分页
	 * 
	 * @param int $page
	 * @param int $perPage
	 */
	public function offsetPaginate(int $page = null, int $perPage = null, $columns = ['*'])
	{
		// 每页条数
		$perPage = $this->model->resolvePerPage($perPage);
		// 当前页
		$page = $this->model->resolveCurrentPage($page);

		// 默认
		$results = $this->model->newCollection();

		if ($total = $this->count($isResetSelect = false)) {
			// 分页
			$results = $this->page($page, $perPage)
				// 查询
				->get($columns);
		}

		return new Paginator($results, $total, $perPage, $currentPage = $page);

		//         $results = $total
        //     ? $this->forPage($page, $perPage)->get($columns)
        //     : $this->model->newCollection();

        // return $this->paginator($results, $total, $perPage, $page, [
        //     'path' => Paginator::resolveCurrentPath(),
        //     'pageName' => $pageName,
        // ]);
		//         return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
        //     'items', 'total', 'perPage', 'currentPage', 'options'
        // ));
		// 	return $this->model::paginator($results, $total, $perPage, $page);
	}


	// /**
	//  * 偏移量分页
	//  * 
	//  * @param int|null $perPage
	//  * @param array $columns
	//  * @param string $pageName
	//  * @param int|null $page
	//  * @return object
	//  */
    // public function paginate(int $perPage = null, $columns = [], string $pageName = 'page', int $page = null)
    // {
	// 	// 当前页
    //     $page = $page ?: $this->model::resolveCurrentPage($pageName);

	// 	// 每页条数
    //     $perPage = $perPage ?: $this->model->getPerPage();

	// 	$results = ($total = $this->count($isResetSelect = false))
	// 					? $this->page($page, $perPage)->get($columns)
	// 					: new \stdclass();

	// 	return $this->model::paginator($results, $total, $perPage, $page);
    // }


// ---------------------- 删除(D) ----------------------
	/**
	 * 强制删除
	 * 
	 * @return int
	 */
	public function forceDelete()
	{
		// 获取 编译后 删除 SQL
		$sql = $this->query->get_compiled_delete();
		if ($sql === false) {
			throw new ModelException(
				get_class($this) . ' --> ' . ($this->errorMessage() ?: 'compiles delete sql failed')
			);
		}

		// 执行 SQL
		$query = $this->query->query($sql);
		if ($query === false) {
			throw new ModelException(
				get_class($this) . ' --> ' . ($this->errorMessage() ?: 'delete failed')
			);
		}

		return $this->query->affected_rows();
	}



	//////////////////////////////////////////////////////

	// /**
	//  * 插入
	//  * 
	//  * @param array $values
	//  * @return int
	//  */
	// public function insert(array $values)
	// {
	// 	if (! empty($values)) {
	// 		// 转为 二维数组
	// 		if (! is_array(reset($values))) {
	// 			$values = [$values];
	// 		}

	// 		// 时间戳
	// 		if ($this->model->usesTimestamps()) {
	// 			$values = $this->model->addTimestampsColumn($values);
	// 		}

	// 		$rows = $this->set_insert_batch($values)->insert_batch();
	// 		if (! $rows) {
	// 			throw new ModelException(
	// 				get_class($this->model) . ' --> ' . ($this->message() ?: 'insert failed')
	// 			);
	// 		}
	// 	}

	// 	return $rows;
	// }

// ---------------------- 读取(R) ----------------------
	// /**
	//  * 总条数
	//  * 
	//  * @param bool $isResetSelect
	//  * @return int
	//  */
	// public function count($isResetSelect = true)
	// {
	// 	// 软删除 开启
	// 	// if ($this->model->useSoftDelete()) {
	// 	// 	$this->softDeleteWhere();
	// 	// }

	// 	return $this->count_all_results('', $isResetSelect);
	// }

	// /**
	//  * 查询 记录
	//  * 
	//  * @param array|string $columns
	//  * @return object
	//  */
	// public function get($columns = [])
	// {
	// 	// 软删除 开启
	// 	// if ($this->model->useSoftDelete()) {
	// 	// 	$this->softDeleteWhere();
	// 	// }

	// 	// 执行查询
	// 	// $query = $this->select($columns)->get_where();
	// 	$query = $this->applyScopes()->select($columns)->get_where();
	// 	if (! $query) {
	// 		throw new ModelException(
	// 			get_class($this->model) . ' --> ' . ($this->message() ?: 'select failed')
	// 		);
	// 	}

	// 	// return $this->transformValue($query->result_array());
	// 	return (object)$query->result();
	// }

	// /**
	//  * 偏移量分页
	//  * 
	//  * @param int|null $perPage
	//  * @param array $columns
	//  * @param string $pageName
	//  * @param int|null $page
	//  * @return object
	//  */
    // public function paginate(int $perPage = null, $columns = [], string $pageName = 'page', int $page = null)
    // {
	// 	// 当前页
    //     $page = $page ?: $this->model::resolveCurrentPage($pageName);

	// 	// 每页条数
    //     $perPage = $perPage ?: $this->model->getPerPage();

	// 	$results = ($total = $this->count($isResetSelect = false))
	// 					? $this->page($page, $perPage)->get($columns)
	// 					: new \stdclass();

	// 	return $this->model::paginator($results, $total, $perPage, $page);
    // }

	// /**
	//  * 唯一 记录
	//  *
	//  * @param  array|string  $columns
	//  * @return object
	//  */
	// public function sole($columns = [])
	// {
	// 	// 执行查询
	// 	$result = $this->limit(2)->get($columns);

	// 	// 记录条数
	// 	$count = count(get_object_vars($result));

	// 	// 不存在
	// 	if ($count === 0) {
	// 		throw new RecordsNotFoundException();
	// 	}

	// 	// 不唯一
	// 	if ($count > 1) {
	// 		throw (new MultipleRecordsFoundException(
	// 			$count, get_class($this->model)
	// 		));
	// 	}

	// 	return reset($result);
	// }

	// /**
	//  * 第一个值
	//  * 
	//  * @param array|string $columns
	//  * @return object
	//  */
	// public function first($columns = [])
	// {
	// 	// 执行查询
	// 	$result = $this->limit(1)->get($columns);

	// 	return reset($result);
	// }

	// /**
	//  * 是否 存在
	//  * 
	//  * @return bool
	//  */
	// public function exists()
	// {
	// 	// 获取 已编译 SELECT 查询 SQL
	// 	$select = $this->get_compiled_select();

	// 	$sql = "SELECT EXISTS ({$select}) AS `exists`";

	// 	// 查询
	// 	if (! $query = $this->query($sql)) {
	// 		throw new ModelException(
	// 			get_class($this->model) . ' --> ' . ($this->message() ?: 'select failed')
	// 		);
	// 	}

	// 	if ($result = $query->row_array()) {
	// 		return (bool)$result['exists'];
	// 	}

	// 	return false;
	// }

	// /**
	//  * 最大值
	//  * 
	//  * @param string $column
	//  * @return mixed
	//  */
	// public function max(string $column)
	// {
	// 	$result = $this->select_max($column, $alias = $column . '_max')->first();

	// 	return $result->$alias;
	// }

	// /**
	//  * 包含已删除
	//  * 
	//  * @return $this
	//  */
	// public function withDeleted()
	// {
	// 	return $this;
	// }

	// /**
	//  * 只有已删除
	//  * 
	//  * @return $this
	//  */
	// public function onlyDeleted()
	// {
	// 	return $this;
	// }

// ---------------------- 更新(U) ----------------------
	// /**
	//  * 更新
	//  * 
	//  * @param array $value
	//  * @return int
	//  */
	// public function update(array $value)
	// {
	// 	// 时间戳
	// 	if ($this->model->usesTimestamps()) {
	// 		$value = $this->model->addUpdatedAtColumn($value);
	// 	}

	// 	if (! $this->query->set($value)->update()) {
	// 		throw new ModelException(
	// 			get_class($this->model) . ' --> ' . ($this->message() ?: 'update failed')
	// 		);
	// 	}

	// 	return $this->affected_rows();
	// }

// ---------------------- 删除(D) ----------------------
	// /**
	//  * 强制删除
	//  * 
	//  * @return int
	//  */
	// public function forceDelete()
	// {
	// 	if (! $this->delete()) {
	// 		throw new ModelException(
	// 			get_class($this->model) . ' --> ' . ($this->message() ?: 'delete failed')
	// 		);
	// 	}

	// 	return $this->affected_rows();
	// }

	// /**
	//  * 运行软删除
	//  * 
	//  * @return int
	//  */
	// public function runSoftDelete()
	// {
	// 	$softDeleteWhere = [];
	// 	if ($softDeleteColumn = $this->model->getSoftDeleteColumn()) {
	// 		$softDeleteWhere[$softDeleteColumn] = $this->model->getSoftDeleteFalseValue();
	// 	}
	// 	if ($deletedAtColumn = $this->model->getDeletedAtColumn()) {
	// 		$softDeleteWhere[$deletedAtColumn] = null;
	// 	}

	// 	$data = [];
	// 	// 删除时间 列
	// 	if ($column = $this->model->getDeletedAtColumn()) {
	// 		// 转为 存储格式
	// 		$data[$column] = $this->model->transformToStorageFormat(
	// 			$this->model->freshTimestamp()
	// 		);
	// 	}
	// 	// 软删除 列
	// 	if ($column = $this->model->getSoftDeleteColumn()) {
	// 		$data[$column] = $this->model->getSoftDeleteTrueValue();
	// 	}

	// 	return $this->wheres($softDeleteWhere)->update($data);
	// }

	// /**
	//  * 删除
	//  * 
	//  * @return int
	//  */
	// public function destroy()
	// {
    //     if ($this->model->useSoftDelete()) {
	// 		return $this->runSoftDelete();
    //     }

	// 	return $this->forceDelete();
	// }

// ---------------------- 全局作用域 ----------------------
	// /**
	//  * 注册 全局作用域
	//  * 
	//  * @param string $identifier
	//  * @param \Closure $scope
	//  * @return $this
	//  */
	// public function withGlobalScope($identifier, $scope)
	// {
    // 	$this->scopes[$identifier] = $scope;

	// 	return $this;
	// }

	// /**
	//  * 应用 作用域
	//  * 
	//  * @return
	//  */
	// public function applyScopes()
	// {
	// 	if (! $this->scopes) {
	// 		return $this;
	// 	}

    // 	$builder = clone $this;

	// 	foreach ($this->scopes as $identifier => $scope) {
    // 		if (! isset($builder->scopes[$identifier])) {
    // 			continue;
	// 		}

    //         $builder->callScope(function (self $builder) use ($scope) {
    //             if ($scope instanceof \Closure) {
    //                 $scope($builder);
    //             }
    //     	});
	// 	}

	// 	return $builder;
	// }

	// /**
	//  * 调用 作用域
	//  * 
	//  * @param callable $scope
	//  * @param array $parameters
	//  * @return mixed
	//  */
	// protected function callscope(callable $scope, array $parameters = [])
	// {
    //     array_unshift($parameters, $this);

    //     $result = $scope(...$parameters) ?? $this;

    //     return $result;
	// }

// ---------------------- 查询构造器 扩展 ----------------------
	/**
	 * 查询 列
	 * 
	 * @param array|string
	 * @return $this
	 */
	public function select($columns = ['*'], $escape = NULL)
	{
		if (is_string($columns)) {
			$columns = explode(',', $columns);
		}

		// 添加 限定
		// $columns = $this->model->qualifyColumns($columns);

		// 设置 查询 列
		$this->query->select($columns, $escape);

		return $this;
	}

	/**
	 * 多 条件
	 * 
	 * AND WHERE
	 * 
	 * @param array $where
	 * @param bool $escape
	 * @return $this
	 */
	public function wheres(array $wheres, $escape = null)
	{
		foreach ($wheres as $column => $value) {
			// 添加 限定
			// $column = $this->model->qualifyColumn($column);

			if (is_array($value)) {
				$this->query->where_in($column, $value, $escape);
			} else {
				$this->query->where($column, $value, $escape);
			}
		}

		return $this;
	}

	/**
	 * 多 模糊匹配
	 * 
	 * AND (LIKE OR LIKE)
	 * 
	 * @param array $columns
	 * @param string $keyword
	 * @param string $siede
	 * @param bool $escape
	 * @return $this
	 */
	public function likeBatch(array $columns = [], string $keyword = null, string $side = 'both', bool $escape = null)
	{
		if ($columns && $keyword) {
			// 条件组 开始
			count($columns) > 1 && $this->group_start();

			$isFirst = true;
			foreach ($columns as $column) {
				// 添加 限定
				// $column = $this->model->qualifyColumn($column);

				// 第一个 AND
				if ($isFirst) {
					$this->query->like($column, $keyword, $side, $escape);
					$isFirst = false;
					continue;
				}

				// 其它 OR
				$this->query->or_like($column, $keyword, $side, $escape);
			}

			// 条件组 结束
			count($columns) > 1 && $this->group_end();
		}

		return $this;
	}

	/**
	 * 多 排序
	 * 
	 * ORDER BY
	 * 
	 * @param array $orderBy
	 * @param bool $escape
	 * @return $this
	 */
	public function orderByBatch(array $orderBy = [], $escape = null)
	{
		foreach ($orderBy as $column => $direction) {
			// 添加 限定
			// $column = $this->model->qualifyColumn($column);

			$this->query->order_by($column, $direction, $escape);
		}

		return $this;
	}

	/**
	 * 多 分组
	 * 
	 * GROUP BY
	 * 
	 * @param array $columns
	 * @param bool $escape
	 * @return $this
	 */
	public function groupByBatch(array $columns = [], $escape = NULL)
	{
		// 添加 限定
		// $columns = $this->model->qualifyColumn($columns);

		// 分组
		$this->query->group_by($columns, $escape);

		return $this;
	}

	/**
	 * 分页
	 * 
	 * @param int $page
	 * @param int $perPage
	 * @return $this
	 */
	public function page(int $page, int $perPage)
	{
		return $this->offset(($page - 1) * $perPage)->limit($perPage);
	}

		// /**
	//  * 分页
	//  * 
	//  * @param int $page
	//  * @param int $perPage
	//  * @return $this
	//  */
	// public function page(int $page, int $perPage)
	// {
	// }



	/**
	 * 错误信息
	 * 
	 * @return string
	 */
	public function errorMessage()
	{
		$message = '';

		// 获取 最后错误
		$error = array_filter($this->query->error());

		if ($error) {
			$message = 'SQL Error';
			if ($error['code'] ?? false) {
				$message .= '(' . $error['code'] . ')';
			}

			if ($error['message'] ?? false) {
				$message .= ': ' . $error['message'];
			}
		}

		return $message;
	}

// ---------------------- PHP 魔术方法 ----------------------
	/**
	 * 
	 * 处理调用 不可访问 成员方法
	 * 
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		// 直通
		$this->passthru = [
			// 'get_compiled_insert',
			// 'query',
			// 'insert_id',
			// 'insert_batch',
			'count_all_results',
			// 'get_where',
			'get_compiled_select',
			// 'query',
			// 'affected_rows',
			// 'delete',
		];
		if (in_array($method, $this->passthru)) {
			return $this->toBase()->{$method}(...$parameters);
		}

		// 转发 调用方法
		$this->forwardCallToObject($this->query, $method, $parameters);

		return $this;
	}

	/**
	 * 处理调用 不可访问 静态方法 
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		var_dump(__FILE__ . ' --> ' . __FUNCTION__ . ' --> '. $method);exit;
	}

}
