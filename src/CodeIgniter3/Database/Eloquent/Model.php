<?php

// 命名空间
namespace Xzb\CodeIgniter3\Database\Eloquent;

use Xzb\CodeIgniter3\Support\Str;
use Xzb\CodeIgniter3\Support\Date;
use Xzb\CodeIgniter3\Support\Traits\ForwardsCalls;

// 数据库 连接
use Xzb\CodeIgniter3\Database\Connection;

// 第三方 日期时间类
use Carbon\Carbon;
use Carbon\CarbonInterface;
// use Carbon\CarbonImmutable;

// PHP 日期时间接口
use DateTimeInterface;
// use DateTimeImmutable;

// 异常类
use Xzb\Exceptions\ModelException;

/**
 * 模型类
 */
abstract class Model
{
	use ForwardsCalls;

// ---------------------- 数据库 ----------------------
	/**
	 * 连接组
	 * 
	 * @var string
	 */
	protected $group = '';

	/**
	 * 获取 连接组
	 * 
	 * @return string
	 */
	public function getConnectionGroup()
	{
		return $this->group;
	}

	/**
	 * 设置 连接组
	 * 
	 * @param string $group
	 * @return $this
	 */
	public function setConnectionGroup(string $group)
	{
		$this->group = $group;

		return $this;
	}

// ---------------------- 数据表 ----------------------
	/**
	 * 关联 数据表
	 * 
	 * @var string
	 */
	protected $table;

	/**
	 * 获取 关联 数据表
	 * 
	 * @return string
	 */
	public function getTable()
	{
		return $this->table ?? Str::snake(Str::plural(
			basename(str_replace('\\', '/', get_class($this)))
		));
	}

	/**
	 * 设置 关联 数据表
	 * 
	 * @param string $table
	 * @return $this
	 */
	public function setTable(string $table)
	{
		$this->table = $table;

		return $this;
	}

// ---------------------- 主键 ----------------------
	/**
	 * 主键 是否自增
	 * 
	 * 默认 自增
	 * 
	 * @var bool
	 */
	public $incrementing = true;

	/**
	 * 主键
	 * 
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * 主键 数据类型
	 * 
	 * @var string
	 */
	protected $primaryKeyType = 'int';

	/**
	 * 获取 主键 是否自增
	 * 
	 * @return bool
	 */
	public function getIncrementing()
	{
		return $this->incrementing;
	}

	/**
	 * 获取 主键名
	 * 
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return $this->primaryKey;
	}

	/**
	 * 设置 主键
	 * 
	 * @param string $key
	 * @return $this
	 */
	public function setPrimaryKeyName(string $key)
	{
		$this->primaryKey = $key;

		return $this;
	}

	/**
	 * 获取 主键 数据类型
	 * 
	 * @return string
	 */
	public function getPrimaryKeyType()
	{
		return $this->primaryKeyType;
	}

	/**
	 * 获取 主键 值
	 * 
	 * @return mixed
	 */
	public function getPrimaryKeyValue()
	{
		return $this->getAttribute($this->getPrimaryKeyName());
	}

	/**
	 * 获取 更新 主键 值
	 * 
	 * @return mixed
	 */
	public function getPrimaryKeyValueForUpdate()
	{
		return $this->original[$this->getPrimaryKeyName()] ?? $this->getPrimaryKeyValue();
	}


    // /**
    //  * Get the primary key value for a save query.
    //  *
    //  * @return mixed
    //  */
    // protected function getKeyForSaveQuery()
    // {
    // }

	// /**
	//  * 获取 
	//  */

		// return $this->original[$this->getPrimaryKeyName()] ?? $this->getKey();
	//     /**
    //  * Get the value of the model's primary key.
    //  *
    //  * @return mixed
    //  */
    // public function getKey()
    // {
    // }

// ---------------------- 查询构造器 ----------------------
	/**
	 * 新建 基础查询构造器
	 * 
	 * @return \CI_DB_query_builder
	 */
	public function newBaseQueryBuilder()
	{
		return (new Connection())->query($this->getConnectionGroup());
	}

	/**
	 * 新建 Eloquent 查询构造器
	 * 
	 * @return Xzb\MasterKey\Frameworks\CodeIgniter3\Builder
	 */
	public function newEloquentBuilder()
	{
		return new Builder($this->newBaseQueryBuilder());
	}

	/**
	 * 新建 无作用域 模型 查询构造器
	 * 
	 * @return Xzb\MasterKey\Frameworks\CodeIgniter3\Builder
	 */
	public function newModelQuery()
	{
		return $this->newEloquentBuilder()
					->setModel($this);
	}

	/**
	 * 新建 作用域 模型 查询构造器
	 * 
	 * @return Xzb\MasterKey\Frameworks\CodeIgniter3\Builder
	 */
	public function newQuery()
	{
		$builder = $this->newModelQuery();

		foreach ($this->getGlobalScopes() as $identifier => $scope) {
			$builder->withGlobalScope($identifier, $scope);
		}

		return $builder;
	}

// ---------------------- 全局 作用域 ----------------------
	/**
	 * 全局 作用域
	 *
	 * @var array
	 */
	protected static $globalScopes = [];

	/**
	 * 注册 全局作用域
	 * 
	 * @param mixed
	 * @return void
	 */
	public static function addGlobalScope($scope, $implementation = null)
	{
		if ($scope instanceof \Closure) {
			return static::$globalScopes[static::class][spl_object_hash($scope)] = $scope;
		}
	}

	/**
	 * 获取 全局作用域
	 * 
	 * @return 
	 */
	public function getGlobalScopes()
	{
        return static::$globalScopes[static::class] ?? [];
	}

// ---------------------- 属性 ----------------------
	/**
	 * 属性
	 * 
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * 原始 属性
	 * 
	 * @var array
	 */
	protected $original = [];

	/**
	 * 更改 属性
	 * 
	 * @var array
	 */
	protected $changes = [];

	/**
	 * 强制转换 属性
	 * 
	 * @var array
	 */
	protected $casts = [
		// 属性 => 数据类型
	];

	/**
	 * 强制转换 数据类型缓存
	 * 
	 * @vara array
	 */
	protected static $castTypeCache = [];

	/**
	 * 是否存在
	 * 
	 * @var bool
	 */
	public $exists = false;

	/**
	 * 构造函数
	 * 
	 * @param array $attributes
	 * @return void
	 */
	public function __construct(array $attributes = [])
	{
		$this->syncOriginal();

		// 填充属性
		$this->fill($attributes);
	}

	/**
	 * 填充 属性
	 * 
	 * @param array $attributes
	 * @return $this
	 */
	public function fill(array $attributes)
	{
		// 循环 设置 属性
		foreach ($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}

		return $this;
	}

	/**
	 * 设置 指定属性
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function setAttribute(string $key, $value)
	{
		// 属性修改器
		if ($this->hasSetMutator($key)) {
			return $this->setMutatorAttributeValue($key, $value);
		}
		// 日期 属性
		else if ($this->isDateAttribute($key)) {
			$value = $this->transformToStorageFormat($value);
		}

		$this->attributes[$key] = $value;

		return $this;
	}

	/**
	 * 设置 原始属性
	 * 
	 * 未进行检测、转换
	 * 
	 * @param array $attributes
	 * @return $this
	 */
	public function setRawAttributes(array $attributes, $sync = false)
	{
		$this->attributes = $attributes;

		if ($sync) {
			$this->syncOriginal();
		}

		return $this;
	}

	/**
	 * 获取 指定属性
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function getAttribute(string $key)
	{
		if (! $key) {
			return;
		}

		return $this->transformModelValue($key, $this->getAttributes()[$key] ?? null);
	}

	/**
	 * 获取 所有属性
	 * 
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * 获取 插入属性
	 * 
	 * @return array
	 */
	public function getInsertAttributes()
	{
		return $this->getAttributes();
	}
	
	// ---------------------- 原始属性 ----------------------
	/**
	 * 同步 原始属性
	 * 
	 * @return $this
	 */
	public function syncOriginal()
	{
		$this->original = $this->getAttributes();

		return $this;
	}

	// ---------------------- 更改属性 ----------------------
	/**
	 * 是否为 脏 属性
	 * 
	 * @param array|string|null
	 * @return bool
	 */
	public function isDirty($attributes = null)
	{
		$attributes = is_array($attributes) ? $attributes : func_get_args();

		return $this->hasChanges($this->getDirty(), $attributes);
	}

	/**
	 * 获取 脏(被修改) 属性
	 * 
	 * @var array
	 */
	public function getDirty()
	{
		$dirty = [];

		foreach ($this->getAttributes() as $key => $value) {
			if (
				// 原始属性中 存在
				array_key_exists($key, $this->original)
				// 恒等 原始属性值
				&& $value === $this->original[$key]
			) {
				continue;
			}

			$dirty[$key] = $value;
		}

		return $dirty;
	}

	/**
	 * 同步 更改 属性
	 * 
	 * @return $this
	 */
	public function syncChanges()
	{
		$this->changes = $this->getDirty();

		return $this;
	}

	/**
	 * 获取 更改 属性
	 * 
	 * @return array
	 */
	public function getChanges()
	{
		return $this->changes;
	}

	/**
	 * 是否有 更改 属性
	 * 
	 * @param array $changes
	 * @param array|string|null
	 * @return bool
	 */
	protected function hasChanges($changes, $attributes = null)
	{
		if (empty($attributes)) {
			return count($changes) > 0;
		}

		$attributes = is_array($attributes) ? $attributes : (array)$attributes;
		foreach ($attributes as $attribute) {
			if (array_key_exists($attribute, $changes)) {
				return true;
			}
		}

		return false;
	}

	// ---------------------- 强制转换 ----------------------
	/**
	 * 获取 强制转换 属性
	 * 
	 * @return array
	 */
	public function getCasts()
	{
		if ($this->getIncrementing()) {
			return array_merge([
				$this->getPrimaryKeyName() => $this->getPrimaryKeyType()
			], $this->casts);
		}

		return $this->casts;
	}

	/**
	 * 是否有 强制转换
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function hasCast(string $key)
	{
		return array_key_exists($key, $this->getCasts());
	}

	/**
	 * 是否为 自定义日期时间 强制转换
	 * 
	 * @param string $cast
	 * @return bool
	 */
	protected function isCustomDateTimeCast(string $cast)
	{
		return strncmp($cast, $str = 'datetime:', strlen($str)) === 0;
	}

	/**
	 * 获取 强制转换 数据类型
	 * 
	 * @param string $key
	 * @return string
	 */
	public function getCastType(string $key)
	{
		// 获取 设置 数据类型
		$castType = $this->getCasts()[$key];

		// 缓存中 存在
		if (isset(static::$castTypeCache[$castType])) {
			return static::$castTypeCache[$castType];
		}

		// 自定义 日期时间
		if ($this->isCustomDateTimeCast($castType)) {
			$convertedCastType = 'custom_datetime';
		}
		else {
			// 转为 小写
			$convertedCastType = trim(strtolower($castType));
		}

		return static::$castTypeCache[$castType] = $convertedCastType;
	}

	// ---------------------- 修改器 ----------------------
	/**
	 * 是否有 set{属性}Attribute 修改器
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function hasSetMutator(string $key)
	{
		return method_exists($this, 'set' . Str::bigCamel($key) . 'Attribute');
	}

	/**
	 * 使用 修改器 设置属性值
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	public function setMutatorAttributeValue(string $key, $value)
	{
		return $this->{'set' . Str::bigCamel($key) . 'Attribute'}($value);
	}

	// ---------------------- 访问器 ----------------------
	/**
	 * 是否有 get{属性}Attribute 访问器
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function hasGetAccessor(string $key)
	{
		return method_exists($this, 'get' . Str::bigCamel($key) . 'Attribute');
	}

	/**
	 * 获取 访问器 属性值
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	public function getAccessorAttributeValue(string $key, $value)
	{
		return $this->{'get' . Str::bigCamel($key) . 'Attribute'}($value);
	}

// ---------------------- 日期 属性 ----------------------
	/**
	 * 日期 属性
	 * 
	 * @var array
	 */
	protected $dates = [];

	/**
	 * 是否 使用 时间戳
	 * 
	 * 默认使用
	 * 
	 * @var bool
	 */
	public $timestamps = true;

	/**
	 * 创建时间 列名
	 * 
	 * @var string|null
	 */
	const CREATED_AT = 'created_at';

	/**
	 * 更新时间 列名
	 * 
	 * @var string|null
	 */
	const UPDATED_AT = 'updated_at';

	/**
	 * 日期列 存储格式
	 * 
	 * 可选格式: https://www.php.net/manual/zh/datetime.format.php
	 * 
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * 是否为 日期 属性
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function isDateAttribute(string $key)
	{
		return in_array($key, $this->getDateAttributes(), true);
    //     return in_array($key, $this->getDates(), true) ||
    //         $this->isDateCastable($key);
	}

	/**
	 * 获取 日期 属性
	 * 
	 * @return array
	 */
	public function getDateAttributes()
	{
		if ($this->usesTimestamps()) {
			return array_unique(array_merge($this->dates, [
				$this->getCreatedAtColumn(),
				$this->getUpdatedAtColumn(),
			]));
		}

		return $this->dates;
	}

	/**
	 * 是否使用 时间戳
	 * 
	 * @return bool
	 */
	public function usesTimestamps()
	{
		return $this->timestamps;
	}

	/**
	 * 获取 创建时间 列名
	 * 
	 * @return string|null
	 */
	public function getCreatedAtColumn()
	{
		return static::CREATED_AT;
	}

	/**
	 * 获取 更新时间 列名
	 * 
	 * @return string|null
	 */
	public function getUpdatedAtColumn()
	{
		return static::UPDATED_AT;
	}

	/**
	 * 获取 日期列 存储格式
	 * 
	 * @return string
	 */
	public function getDateFormat()
	{
		return $this->dateFormat ?: 'Y-m-d H:i:s';
	}

	/**
	 * 更新 创建时间 和 修改时间
	 * 
	 * @return $this
	 */
	public function updateTimestamps()
	{
		// 新时间戳
		$time = $this->freshTimestamp();

		// 更新时间 列
		$updatedAtColumn = $this->getUpdatedAtColumn();
		if (
			// 列 存在
			$updatedAtColumn
			// 未被修改
			&& ! $this->isDirty($updatedAtColumn)
		) {
			$this->setAttribute($updatedAtColumn, $time);
		}

		// 创建时间 列
		$createdAtColumn = $this->getCreatedAtColumn();
		if (
			// 不存在
			! $this->exists
			// 列 存在
			&& $createdAtColumn
			// 未被修改
			&& ! $this->isDirty($createdAtColumn)
		) {
			$this->setAttribute($createdAtColumn, $time);
		}
	}

	/**
	 * 新时间戳
	 * 
	 * @return \Xzb\Support\Date
	 */
	public function freshTimestamp()
	{
		return Date::now();
	}

    // /**
    //  * Determine whether a value is Date / DateTime castable for inbound manipulation.
    //  *
    //  * @param  string  $key
    //  * @return bool
    //  */
    // protected function isDateCastable($key)
    // {
    //     return $this->hasCast($key, ['date', 'datetime', 'immutable_date', 'immutable_datetime']);
    // }

// ---------------------- 属性值 转换 ----------------------
	/**
	 * 转换 模型值
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	protected function transformModelValue(string $key, $value)
	{
		// 属性 访问器
		if ($this->hasGetAccessor($key)) {
			return $this->getAccessorAttributeValue($key, $value);
		}

		// 属性 强制转换
		if ($this->hasCast($key)) {
			return $this->transformCastAttributeValue($key, $value);
		}

		return $value;
	}

	/**
	 * 转换 强制转换 属性值
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param mixed
	 */
	protected function transformCastAttributeValue(string $key, $value)
	{
		// 获取 强制转换 数据类型
		$castType = $this->getCastType($key);

		switch ($castType) {
			// 布尔类型
				case 'bool':
				case 'boolean':
					return (bool)$value;
			// 整形
				case 'int':
				case 'integer':
					return (int)$value;
			// 浮点型
				case 'float':
					return $this->transformToFloat($value);
			// 字符串
				case 'str':
				case 'string':
					return (string)$value;
			// 数组
				case 'arr':
				case 'array':
					return $this->transformToArray($value);
			// 对象
				case 'obj':
				case 'object';
					return $this->transformToObject($value);
			// 时间戳
				case 'timestamp':
					// 格式
					$format = 'U';
					return $this->transformToDateCustomFormat($value, $format);
			// 日期时间
				case 'datetime':
					// 格式
					$format = 'Y-m-d H:i:s';
					return $this->transformToDateCustomFormat($value, $format);
			// 自定义 日期时间
				case 'custom_datetime':
					// 格式
					$format = explode(':', $this->getCasts()[$key], 2)[1];
					return $this->transformToDateCustomFormat($value, $format);
		}

		return $value;
	}

	/**
	 * 转为 浮点
	 * 
	 * @param string $value
	 * @return mixed
	 */
	public function transformToFloat(string $value)
	{
		switch ((string) $value) {
			case 'Infinity':
				return INF;
			case '-Infinity':
				return -INF;
			case 'NaN':
				return NAN;
			default:
				return (float) $value;
		}
	}

	/**
	 * 转为 数组
	 * 
	 * @param string $value
	 * @return array
	 */
	public function transformToArray(string $value)
	{
		// 按 JSON 格式的字符串进行解码
		$arr = json_decode($value, true);
		// 没有错误发生
		if (json_last_error() == JSON_ERROR_NONE) {
			return $arr;
		}

		return $value ? (array)$value : [];
	}

	/**
	 * 转为 对象
	 * 
	 * @param string $value
	 * @return object
	 */
	public function transformToObject(string $value)
	{
		// 按 JSON 格式的字符串进行解码
		$obj = json_decode($value);
		// 没有错误发生
		if (json_last_error() == JSON_ERROR_NONE) {
			return $obj;
		}

		return $value ? (object)$value : new stdClass();
	}

	/**
	 * 转为 存储格式
	 * 
	 * @param mixed $value
	 * @return string|null
	 */
	public function transformToStorageFormat($value)
	{
		return $this->transformToDateCustomFormat($value, $this->getDateFormat());
	}

	/**
	 * 转为 自定义日期格式
	 * 
	 * @param mixed $value
	 * @return string
	 */
	public function transformToDateCustomFormat($value, $format)
	{
		return empty($value)
				? $value
				: $this->transformToDateTimeObject($value)->format($format);
	}

	/**
	 * 转为 DateTime 对象
	 * 
	 * @param mixed $value
	 * @return \Xzb\Support\Date
	 */
	protected function transformToDateTimeObject($value)
	{
		// 第三方库 Carbon实例
		if ($value instanceof CarbonInterface) {
			// 返回 Carbon实例
			return Date::instance($value);
		}

		// PHP库 DateTime实例
		if ($value instanceof DateTimeInterface) {
			// 按 日期格式、时区 解析 返回 Carbon实例
			return Date::parse(
				$value->format('Y-m-d H:i:s.u'), $value->getTimezone()
			);
		}

		// 数字或数字字符串
		if (is_numeric($value)) {
			// 以 UNIX时间戳 创建 Carbon实例
			return Date::createFromTimestamp($value);
		}

		// 标准 日期 格式
		if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
			// 返回 Carbon实例
			return Date::instance(
				// 根据 标准日期格式 解析 返回 日期时间格式 例如：2012-01-31 00:00:00
				Carbon::createFromFormat('Y-m-d', $value)->startOfDay()
			);
		}

		// 获取 日期列 存储格式
		$format = $this->getDateFormat();
		try {
			// 根据 存储格式 创建 Carbon实例
			$date = Date::createFromFormat($format, $value);
		} catch (InvalidArgumentException $e) {
			// 无效参数异常
			$date = false;
		}

		return $date ?: Date::parse($value);
	}

// ---------------------- 模型实例 ----------------------
	/**
	 * 创建 新模型实例
	 * 
	 * @param array $attributes
	 * @param bool $exists
	 * @return static
	 */
	public function  newInstance(array $attributes = [], bool $exists = false)
	{
		$model = new static($attributes);

		$model->exists = $exists;

		return $model;
	}

	/**
	 * 创建 存在的 新模型实例
	 * 
	 * @param array $attributes
	 * @return static
	 */
	public function newExistInstance(array $attributes = [])
	{
		// 创建 新模型实例
		$model = $this->newInstance([], true)
					// 设置 原始属性
					->setRawAttributes($attributes, true);

		return $model;
	}

	/**
	 * 创建 模型 Collection实例
	 * 
	 * @param array $models
	 * @return 
	 */
	public function newCollection(array $models = [])
	{
		return new Collection($models);
	}

// ---------------------- 转换 ----------------------

	/**
	 * 属性 转为 数组
	 * 
	 * @return array
	 */
	public function attributesToArray()
	{
		return $this->getAttributes();
	}

	/**
	 * 转为 数组
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return $this->attributesToArray();
	}










	////////////////////////////////////////////////////////////////////




	// /**
	//  * 是否开启 软删除
	//  * 
	//  * @var bool
	//  */
	// protected $softDelete = false;

	// /**
	//  * 软删除 查询类型
	//  * 
	//  * 包含 已删除: withDeleted
	//  * 只有 已删除: onlyDeleted
	//  * 只有 未删除: withoutDeleted
	//  * 默认: apply
	//  * 
	//  * @var string
	//  */
	// protected $softDeleteQueryType = 'apply';

	// /**
	//  * 软删除 列名
	//  * 
	//  * @var string|null
	//  */
	// const SOFT_DELETE = 'is_deleted';

	// /**
	//  * 软删除 未删除 值
	//  * 
	//  * @var mixed
	//  */
	// protected $softDeleteFalseValue = 0;

	// /**
	//  * 软删除 已删除 值
	//  * 
	//  * @var mixed
	//  */
	// protected $softDeleteTrueValue = 1;




	// /**
	//  * 删除时间 列名
	//  * 
	//  * @var string|null
	//  */
	// const DELETED_AT = 'deleted_at';



	// /**
	//  * 构造函数
	//  * 
	//  * @return void
	//  */
	// public function __construct()
	// {
	// 	if ($this->useSoftDelete()) {
	// 		static::bootSoftDelete();
	// 	}
	// }




// ---------------------- 软删除 ----------------------
	// /**
	//  * 是否 开启 软删除
	//  * 
	//  * @return bool
	//  */
	// public function useSoftDelete()
	// {
	// 	return $this->softDelete;
	// }

	// /**
	//  * 获取 软删除 查询类型
	//  * 
	//  * @return string
	//  */
	// public function getSoftDeleteQueryType()
	// {
	// 	return $this->softDeleteQueryType;
	// }

	// /**
	//  * 设置 软删除 查询类型
	//  * 
	//  * @param string $type
	//  * @return $this
	//  */
	// public function setSoftDeleteQueryType(string $type)
	// {
	// 	$this->softDeleteQueryType = $type;

	// 	return $this;
	// }

	// /**
	//  * 获取 软删除 列名
	//  * 
	//  * @return string|null
	//  */
	// public function getSoftDeleteColumn()
	// {
	// 	return static::SOFT_DELETE;
	// }

	// /**
	//  * 获取 软删除 已删除值
	//  * 
	//  * @return mixed
	//  */
	// public function getSoftDeleteTrueValue()
	// {
	// 	return $this->softDeleteTrueValue;
	// }

	// /**
	//  * 获取 软删除 未删除值
	//  * 
	//  * @return mixed
	//  */
	// public function getSoftDeleteFalseValue()
	// {
	// 	return $this->softDeleteFalseValue;
	// }

	// /**
	//  * 启用 软删除
	//  * 
	//  * @return void
	//  */
	// public static function bootSoftDelete()
	// {
	// 	static::addGlobalScope(function ($builder) {
	// 		$type = $builder->getModel()->getSoftDeleteQueryType();

	// 		switch ($type) {
	// 			// 包含 已删除
	// 			case 'withDeleted':
	// 				break;
	// 			// 只有 已删除
	// 			case 'onlyDeleted':
	// 				break;
	// 			// 未删除
	// 			case 'withoutDeleted':
	// 			// 应用
	// 			case 'apply':
	// 				$softDeleteWhere = [];
	// 				if ($softDeleteColumn = $builder->getModel()->getSoftDeleteColumn()) {
	// 					$softDeleteWhere[$softDeleteColumn] = $builder->getModel()->getSoftDeleteFalseValue();
	// 				}
	// 				if ($deletedAtColumn = $builder->getModel()->getDeletedAtColumn()) {
	// 					$softDeleteWhere[$deletedAtColumn] = null;
	// 				}
    //     			$builder->wheres($softDeleteWhere);
	// 				break;
	// 		}
	// 	});
	// }

// ---------------------- 时间戳 ----------------------


	// /**
	//  * 获取 删除时间 列名
	//  * 
	//  * @return string|null
	//  */
	// public function getDeletedAtColumn()
	// {
	// 	return static::DELETED_AT;
	// }


	// /**
	//  * 添加 时间 列
	//  * 
	//  * @param array $value
	//  * @return array
	//  */
	// public function addTimestampsColumn(array $value)
	// {
	// 	// 数据 转为 二维数组
	// 	$values = is_array(reset($value)) ? $value : [$value];

	// 	// 转为 存储格式
	// 	$time = $this->transformToStorageFormat($this->freshTimestamp());

	// 	// 循环 添加
	// 	foreach ($values as &$val) {
	// 		// 创建时间
	// 		if ($column = $this->getCreatedAtColumn()) {
	// 			$val = array_merge([ $column => $time], $val);
	// 		}

	// 		// 更新时间
	// 		if ($column = $this->getUpdatedAtColumn()) {
	// 			$val = array_merge([ $column => $time], $val);
	// 		}
	// 	}

	// 	$value = is_array(reset($value)) ? $values : reset($values);

	// 	return $value;
	// }

	// /**
	//  * 添加 更新时间 列
	//  * 
	//  * @param array $value
	//  * @return array
	//  */
	// public function addUpdatedAtColumn(array $value)
	// {
	// 	if ($column = $this->getUpdatedAtColumn()) {
	// 		// 数据 转为 二维数组
	// 		$values = is_array(reset($value)) ? $value : [$value];

	// 		// 转为 存储格式
	// 		$time = $this->transformToStorageFormat($this->freshTimestamp());

	// 		// 循环 添加 更新时间
	// 		foreach ($values as &$val) {
	// 			$val = array_merge([ $column => $time], $value);
	// 		}

	// 		return is_array(reset($value)) ? $values : reset($values);
	// 	}

	// 	return $value;
	// }



// ---------------------- 分页 ----------------------
	/**
	 * 每页条数
	 * 
	 * @var int
	 */
	protected $perPage = 15;

	/**
	 * 获取 每页条数
	 * 
	 * @return int
	 */
	public function getPerPage()
	{
		return $this->perPage;
	}

	/**
	 * 设置 每页条数
	 * 
	 * @param int $perPage
	 * @return $this
	 */
	public function setPerPage(int $perPage)
	{
		$this->perPage = $perPage;

		return $this;
	}

	/**
	 * 解析 每页条数
	 * 
	 * @param mixed $perPage
	 * @return int
	 */
	public function resolvePerPage(int $perPage)
	{
		if (filter_var($perPage, FILTER_VALIDATE_INT) !== false && (int)$perPage >= 1) {
			return (int)$perPage;
		}

		return $this->getPerPage();
	}

	/**
	 * 解析 当前页码
	 * 
	 * @param mixed $page
	 * @param int $default
	 * @return int
	 */
	public function resolveCurrentPage($page, int $default = 1)
	{
		if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
			return (int) $page;
		}

		return $default;
	}

	// /**
	//  * 分页器
	//  * 
	//  * @param object $result
	//  * @param int $total
	//  * @param int $perPage
	//  * @param int $currentPage
	//  * @return object
	//  */
	// public static function paginator(object $results, int $total, int $perPage, int $currentPage)
	// {
	// 	return (object)[
	// 		'total'			=> $total,
	// 		'per_page'		=> $perPage,
	// 		'current_page'	=> $currentPage,
	// 		'last_page'		=> max((int) ceil($total / $perPage), 1),
	// 		'data'			=> $results
	// 	];
	// }


// ---------------------- 列 ----------------------
	/**
	 * 限定 列
	 * 
	 * @param string $column
	 * @return string
	 */
	public function qualifyColumn(string $column)
	{
		$column = trim($column);

		if (str_contains($column, '.')) {
			return $column;
		}

		if (strpos($column, $leftParen = '(') !== false) {
    		preg_match_all('/\((.+?)\)/', $column, $matches);
			foreach ($matches[1] as $value) {
				if (trim($value) != '*') {
					$column = str_replace($value, $this->getTable() . '.' . trim($value), $column);
				}
			}

			return $column;
		}

		return $this->getTable() . '.' . $column;
	}

	/**
	 * 限定 列
	 * 
	 * @param array $columns
	 * @return array
	 */
	public function qualifyColumns(array $columns)
	{
		return array_map(function ($column) {
			return $this->qualifyColumn($column);
		}, $columns);
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
		var_dump(__FILE__ . ' --> ' . $method);

        // 转发 调用方法
        // return $this->forwardCallToObject($this->query(), $method, $parameters);
        return $this->forwardCallToObject($this->newQuery(), $method, $parameters);
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
		// 静态方法 转为 动态方法
		return (new static)->$method(...$parameters);
	}

	/**
	 * 动态 获取 属性
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->getAttribute($key);
	}

	/**
	 * 动态 设置 属性
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->setAttribute($key, $value);
	}

}
