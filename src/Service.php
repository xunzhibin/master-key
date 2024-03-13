<?php

// 命名空间
namespace Xzb\MasterKey;

use Xzb\MasterKey\Exceptions\RecordsNotFoundException;
use Xzb\MasterKey\Exceptions\NotExistException;

// 排序解析
use Xzb\Support\Sort;
use Xzb\Support\Str;

/**
 * 基础 业务类
 * 
 */
abstract class Service
{
	use Traits\HasUniques;
	use Traits\HasSorts;
	use Traits\HasKeywords;

	/**
	 * 模型类 实例
	 * 
	 * @var Xzb\MasterKey\Model
	 */
	protected $model;

// --------------------------- 创建(C) ---------------------------
	/**
	 * 创建
	 * 
	 * @param array $data
	 * @param bool $isRefresh
	 * @return object
	 */
	public function create(array $data, bool $isRefresh = false)
	{
		// 验证 唯一
		if ($this->uniqueWriteVerify) {
    		$this->verifyInsertUnique($data);
		}

		// 添加 排序列
        if ($this->insertSorting) {
            $data = $this->addInsertSort($data);
        }

		$result = $this->model->create($data);

		if ($isRefresh) {
			$result = $this->model->wheres([
				$this->model->getPrimaryKeyName() => $result->{$this->model->getPrimaryKeyName()}
			])->first();
		}

		return $result;
		// return $this->model->create($data);
	}

	/**
	 * 插入
	 * 
	 * @param array $datas
	 * @return int
	 */
	public function insert(array $datas)
	{
		// 验证 唯一
		if ($this->uniqueWriteVerify) {
    		$this->verifyInsertUnique($datas);
		}

		// 添加 排序列
        if ($this->insertSorting) {
            $datas = $this->addInsertSort($datas);
        }

		return $this->model->insert($datas);
	}

// --------------------------- 查询 ---------------------------
	/**
	 * 查询 记录条数
	 * 
	 * @param array $keyvalue
	 * @param string $keyword
	 * @return int
	 */
	public function count(array $keyvalue = [], string $keyword = null)
	{
		return $this->model
					->wheres($keyvalue)
					->likeGroup($this->getLikes(), $keyword)
					->count();
	}

	/**
	 * 筛选
	 * 
	 * @param array $keyvalue
	 * @param string $keyword
	 * @param string $sort
	 * @param array|string $columns
	 * @param int $limit
	 */
	public function filter(
		array $keyvalue = [], string $keyword = null, string $sort = null,
		$columns = ['*'], int $limit = null
	)
	{
		return $this->model
					->wheres($keyvalue)
					->likeGroup($this->getLikes(), $keyword)
					->orderByGroup(Sort::decode($sort ?: $this->getQuerySort()))
					->limit($limit)
					->get($columns);
	}

	/**
	 * 偏移量 分页
	 * 
	 * @param array $keyvalue
	 * @param string $keyword
	 * @param string $sort
	 * @param int $perPage
	 * @param array|string $columns
	 * @param string $pageName
	 * @param int $page
	 * @return object
	 */
	public function offsetPaginate(
		array $keyvalue = [], string $keyword = null, string $sort = null,
		int $perPage = null, $columns = ['*'], $pageName = 'page', int $page = null
	)
	{
		return $this->model
					->wheres($keyvalue)
					->likeGroup($this->getLikes(), $keyword)
					->orderByGroup(Sort::decode($sort ?: $this->getQuerySort()))
					->paginate($perPage, $columns, $pageName, $page);
	}

	/**
	 * 唯一记录
	 * 
	 * @param array $keyvalue
	 * @param array|string $columns
	 * @return array 
	 */
	public function sole(array $keyvalue = [], $columns = ['*'])
	{
		try {
			return $this->model->wheres($keyvalue)->sole($columns);
		} catch (RecordsNotFoundException $e) {
			$message = 'No query results for model [{' . $model = get_class($this->model) . '}]';
			throw (new NotExistException($message))->setLabel(Str::snake(basename($model)));
		}
	}

	/**
	 * 按 主键 查询 唯一记录
	 * 
	 * @param mixed $value
	 * @return array
	 */
	public function soleByPrimaryKey($value, $columns = ['*'])
	{
		return $this->sole([
			$this->model->getPrimaryKeyName() => $value
		], $columns);
	}

	/**
	 * 是否存在
	 * 
	 * @param array $keyvalue
	 * @return bool
	 */
	public function exists(array $keyvalue = [])
	{
		return $this->model->wheres($keyvalue)->exists();
	}

	/**
	 * 最大值
	 * 
	 * @param array $keyvalue
	 * @param string $column
	 * @return mixed
	 */
	public function max(string $column, array $keyvalue = [])
	{
		return $this->model->wheres($keyvalue)->max($column);
	}

// --------------------------- 更新 ---------------------------
	/**
	 * 更新记录
	 * 
	 * @param array $keyvalue
	 * @param array $data
	 * @return int
	 */
	public function update(array $keyvalue, array $data)
	{
		return $this->model
					->wheres($keyvalue)
					->update($data);
	}

	/**
	 * 更新 唯一记录
	 * 
	 * @param array $keyvalue
	 * @param array $data
	 * @param array $uniques
	 * @return object
	 */
	public function updateSole(array $keyvalue, array $data)
	{
		// 按 键值 查询 唯一记录
		$info = (array)$this->sole($keyvalue);

		// 获取 变更值
		$changes = array_diff_assoc($data, $info);

		// 无变化
		if (! $changes) {
			return $info;
		}
		
		// 验证 是否唯一
		if ($this->uniqueWriteVerify) {
			$this->verifyUpdateUnique($changes, $info);
		}

		// 按 键值 更新记录
		$this->update($keyvalue, $changes);

		return (object)array_merge($info, $changes);
	}

	/**
	 * 按 主键 更新 唯一记录
	 * 
	 * @param mixed $pk
	 * @param array $data
	 * @param array $uniques
	 * @return object
	 */
	public function updateSoleByPrimaryKey($pk, array $data)
	{
		return $this->updateSole([
			$this->model->getPrimaryKeyName() => $pk
		], $data);
	}

// --------------------------- 删除 ---------------------------
	/**
	 * 删除 记录
	 * 
	 * @param array $keyvalue
	 * @return int
	 */
	public function delete(array $keyvalue)
	{
		return $this->model
					->wheres($keyvalue)
					->destroy();
	}

	/**
	 * 按 主键 删除 记录
	 * 
	 * @param mixed $pk
	 * @return int
	 */
	public function destroy($pk)
	{
        $pk = is_array($pk) ? $pk : func_get_args();

		return $this->delete([
			$this->model->getPrimaryKeyName() => $pk
		]);
	}

// --------------------------- 组合 ---------------------------
    /**
     * 唯一记录 或 创建
     * 
     * @param array $keyvalue
	 * @param array $data
	 * @param bool $isCreateRefresh
	 * @return object
     */
    public function soleOrCreate(array $keyvalue, array $data, bool $isCreateRefresh = false)
    {
		try {
			return $this->sole($keyvalue);
		} catch (NotExistException $e) {
			// 不存在 创建
			return $this->create(array_merge($keyvalue, $data), $isCreateRefresh);
		}
    }

	/**
	 * 更新 或 创建
	 * 
	 * @param array $keyvalue
	 * @param array $data
	 * @param bool $isCreateRefresh
	 * @return object
	 */
	public function updateOrCreate(array $keyvalue, array $data, bool $isCreateRefresh = false)
	{
		try {
			return $this->updateSole($keyvalue, array_merge($keyvalue, $data));
		} catch (NotExistException $e) {
			// 不存在 创建
			return $this->create(array_merge($keyvalue, $data), $isCreateRefresh);
		}
	}

}
