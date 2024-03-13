# master-key
万能钥匙



### 业务类 引用
```php
use Xzb\MasterKey\Service;

/**
 * 用户 业务类
 * 
 */
class UserService extends Service
{

}
```
或者
```php
/**
 * 用户 业务类
 * 
 */
class UserService extends \Xzb\MasterKey\Service
{

}
```

### 业务类 属性
```php
	/**
	 * 模型类 实例
	 */
	protected $model;

	/**
	 * 唯一
	 * 
	 * 逻辑上，某些列的数据, 在DB中是唯一的、不能重复的
	 * 
	 * 	单个
	 * 		[ 'column1', ... ]
	 * 	多个
	 * 		[
	 * 			[ 'column' ],
	 * 			[ 'column1', ... ],
	 * 		]
	 * 
	 * @var array
	 */
	protected $uniques = [];

	/**
	 * 是否 启用 唯一写入验证
	 * 
	 * @var bool
	 */
	protected $uniqueWriteVerify = false;

	/**
	 * 排序
	 * 
	 * DB中数据排序列
	 * 
	 * 	单个 全表排序
	 * 		[ 'column' => [] ]
	 * 	单个 条件排序
	 * 		[ 'column' => [ '条件1', ... ] ]
	 * 	多个
	 * 		[
	 * 			'column1' => [],
	 * 			'column2' => [ '条件1', ... ],
	 * 		]
	 * 
	 * @var array
	 */
	protected $sorts = [];

	/**
	 * 插入 是否添加排序
	 * 
	 * @var bool
	 */
	protected $insertSorting = false;

	/**
	 * 查询 排序
	 * 
	 * 批量查询时, 排序设置
	 * 	升序: +
	 * 	降序: -
	 * 	分隔符: ,
	 * 
	 * 例如: 按 id降序 created_at升序 --> -id,+created_at
	 * 
	 * @var string
	 */
	protected $querySort = '-id';

	/**
	 * 模糊匹配
	 * 
	 * 关键字 在DB中模糊匹配列
	 * 
	 * 	单列
	 * 		[ 'column' ]
	 * 	多列
	 * 		[ 'column1', ... ]
	 * 
	 * @var array
	 */
	protected $likes = [];
```


### 业务类 调用方法
`getUniques()`
参数:
返回:
	唯一
返回类型:
	array

`setUnique()`
参数:
	$unique (mixed) -- 唯一
返回:
	$this
返回类型:
	object

`setRawUniques()`
参数:
	$uniques (array) -- 唯一
返回:
	$this
返回类型:
	object

`resetUniques()`
返回:
	$this
返回类型:
	object

`getUniqueKeyNames()`
返回:
	唯一列名称
返回类型:
	array

`EnableUniqueWriteVerify()`
返回:
	$this
返回类型:
	object

`disableUniqueWriteVerify()`
返回:
	$this
返回类型:
	object

`verifyInsertUnique($data)`
参数:
	$data (array) -- 插入 数据
返回:
	验证结果
返回类型:
	bool

`verifyUpdateUnique(array $changes, array $original)`
参数:
	$changes (array) -- 更新后 数据
	$original (array) -- 原始 数据
返回:
	验证结果
返回类型:
	bool

`getSorts()`
返回:
	排序列
返回类型:
	array

`setSort($key, $where)`
参数:
	$key (string) -- 排序列名
	$where (array) -- 排序 条件
返回:
	$this
返回类型:
	object

`setRawSorts($sorts)`
参数:
	$sorts (array) -- 排序列
返回:
	$this
返回类型:
	object

`resetSorts()`
返回:
	$this
返回类型:
	object

`enableInsertSort()`
返回:
	$this
返回类型:
	object

`disableInsertSort()`
返回:
	$this
返回类型:
	object

`setQuerySort($sort)`
参数:
	$sort (string) -- 查询排序
返回:
	$this
返回类型:
	object

`getQuerySort()`
返回:
	查询排序
返回类型:
	string

`addInsertSort($data)`
参数:
	$data (array) -- 插入数据
返回:
	添加排序列 插入数据
返回类型:
	array

`addSort($data, $columnName[, $filterColumnNames = []])`
参数:
	$data (array) -- 数据
	$columnName (string) -- 排序列名
	$filterColumnNames (array) -- 排序 条件列名
返回:
	添加排序列 插入数据
返回类型:
	array

`getSortNumber($columnName[, $filter = []])`
参数:
	$columnName (string) -- 排序列名
	$filter (array) -- 排序 条件
返回:
	排序编号
返回类型:
	int

`getLikes()`
返回:
	模糊匹配 列
返回类型:
	array

`setLike($column)`
参数:
	$column (string) -- 模糊匹配列名
返回:
	$this
返回类型:
	object

`setRawLikes($likes)`
参数:
	$likes (array) -- 模糊匹配列名
返回:
	$this
返回类型:
	object

`resetLikes($likes)`
返回:
	$this
返回类型:
	object

`create($data, [$isRefresh = false])`
参数:
	$data (array) -- 插入数据
	$isRefresh (bool) -- 插入成功后, 是否根据主键重新查询记录
返回:
	插入成功后记录
返回类型:
	object

`insert($datas)`
参数:
	$data (array) -- 插入数据
返回:
	插入记录行数
返回类型:
	int

`count([ $keyvalue = [], $keyword = null ])`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$keyword (string) -- 按 关键字 模糊匹配
返回:
	返回过滤后记录行数
返回类型:
	int

`filter([ $keyvalue = [], $keyword = null, $sort = null, $columns = ['*'], $limit = null ])`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$keyword (string) -- 按 关键字 模糊匹配
	$sort (string) -- 排序
	$columns (array|string) -- 查询 列
	$limit (int) -- 查询 行数
返回:
	返回过滤后指定行数的记录
返回类型:
	object

`offsetPaginate([ $keyvalue = [], $keyword = null, $sort = null, $perPage = null, $columns = ['*'], $pageName = 'page', $page = null ])`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$keyword (string) -- 按 关键字 模糊匹配
	$sort (string) -- 排序
	$perPage (int) -- 查询 行数
	$columns (array|string) -- 查询 列
	$pageName (string) -- 分页页码字段键名
	$page (int) -- 分页页码
返回:
	过滤后指定行数的记录和分页信息
返回类型:
	object

`sole([$keyvalue = [], $columns = ['*']])`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$columns (array|string) -- 查询 列
返回:
	过滤后 唯一记录
返回类型:
	object

`soleByPrimaryKey($pk[, $columns = ['*']])`
参数:
	$pk (mixed) -- 主键值
	$columns (array|string) -- 查询 列
返回:
	按 主键 过滤后 唯一记录
返回类型:
	object

`exists([$keyvalue = []])`
参数:
	$keyvalue (array) -- 按 键值 过滤
返回:
	过滤后 记录 是否存在
返回类型:
	bool

`max($column[, $keyvalue = []])`
参数:
	$column (array) -- 最大值 列名称
	$keyvalue (array) -- 按 键值 过滤
返回:
	过滤后 最大值
返回类型:
	string

`update($keyvalue, $data)`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$data (array) -- 更新 数据
返回:
	更新记录行数
返回类型:
	int

`updateSole($keyvalue, $data)`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$data (array) -- 更新 数据
返回:
	更新后 唯一记录
返回类型:
	object

`updateSoleByPrimaryKey($pk, $data)`
参数:
	$pk (mixed) -- 主键值
	$data (array) -- 更新 数据
返回:
	按 主键 更新后 唯一记录
返回类型:
	object

`delete($keyvalue)`
参数:
	$keyvalue (array) -- 按 键值 过滤
返回:
	删除记录行数
返回类型:
	int

`destroy($pk)`
参数:
	$pk (mixed) -- 主键值
返回:
	删除记录行数
返回类型:
	int

`soleOrCreate($keyvalue, $data, $isCreateRefresh = false)`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$data (array) -- 插入 数据
	$isCreateRefresh (bool) -- 插入成功后, 是否根据主键重新查询记录
返回:
	过滤后 唯一记录 或 插入 记录
返回类型:
	int

`updateOrCreate($keyvalue, $data, $isCreateRefresh = false)`
参数:
	$keyvalue (array) -- 按 键值 过滤
	$data (array) -- 插入 数据
	$isCreateRefresh (bool) -- 插入成功后, 是否根据主键重新查询记录
返回:
	更新 或 插入 记录
返回类型:
	int


### 模型类 引用
```php
use Xzb\MasterKey\Model;

/**
 * 用户 模型类
 * 
 */
class User extends Model
{

}
```
或者
```php
/**
 * 用户 模型类
 * 
 */
class User extends \Xzb\MasterKey\Model
{

}
```

### 模型类 属性
```php
	/**
	 * CodeIgniter3 DB 连接组
	 * 
	 * @var string
	 */
	protected $group = '';

	/**
	 * 关联 数据表
	 * 
	 * @var string
	 */
	protected $table;

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
	 * 是否 使用 时间戳
	 * 
	 * 默认使用
	 * 
	 * @var bool
	 */
	public $timestamps = true;

	/**
	 * 日期列 存储格式
	 * 
	 * @var string
	 */
	protected $dateFormat;

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
	 * 每页条数
	 * 
	 * @var int
	 */
	protected $perPage = 15;
```

### 模型类 调用方法
`getConnectionGroup()`
返回:
	连接DB组
返回类型:
	string

`setConnectionGroup($group)`
参数:
	$group (string) -- 连接DB组
返回:
	$this
返回类型:
	object

`getTable()`
返回:
	数据表
返回类型:
	string

`setTable($table)`
参数:
	$table (string) -- 数据表
返回:
	$this
返回类型:
	object

`getIncrementing()`
返回:
	主键 是否自增
返回类型:
	bool

`getPrimaryKeyName()`
返回:
	主键名
返回类型:
	string

`setPrimaryKeyName($key)`
参数:
	$key (string) -- 主键名
返回:
	$this
返回类型:
	object

`getPrimaryKeyType()`
返回:
	主键 数据类型
返回类型:
	string

`usesTimestamps()`
返回:
	是否使用 时间戳
返回类型:
	bool

`getCreatedAtColumn()`
返回:
	创建时间 列名
返回类型:
	string

`getUpdatedAtColumn()`
返回:
	更新时间 列名
返回类型:
	string

`getDateFormat()`
返回:
	日期列 存储格式
返回类型:
	string

`addTimestampsColumn($value)`
参数:
	$value (array) -- 数据
返回:
	添加 创建时间和更新时间后 数据
返回类型:
	array

`addUpdatedAtColumn($value)`
参数:
	$value (array) -- 数据
返回:
	添加 更新时间列 数据
返回类型:
	array

`freshTimestamp()`
返回:
	新时间戳
返回类型:
	object

`getPerPage()`
返回:
	每页行数
返回类型:
	int

`setPerPage($perPage)`
参数:
	$perPage (int) -- 每页行数
返回:
	$this
返回类型:
	object

`static resolveCurrentPage([$pageName = 'page', $default = 1])`
参数:
	$pageName (string) -- 页码字段键名
	$default (int) -- 默认 页码值
返回:
	当前页码
返回类型:
	int

`static paginator($results, $total, $perPage, $currentPage)`
参数:
	$results (object) -- 结果
	$total (int) -- 总行数
	$perPage (int) -- 每页行数
	$currentPage (int) -- 当前页码
返回:
	查询记录和分页 数据
返回类型:
	object

`create($value)`
参数:
	$value (array) -- 数据
返回:
	插入 数据
返回类型:
	object

`insert($values)`
参数:
	$values (array) -- 数据
返回:
	插入 行数
返回类型:
	int

`count([$isResetSelect = true])`
参数:
	$isResetSelect (bool) -- 是否刷新查询构造器
返回:
	查询行数
返回类型:
	int

`get([$columns = []])`
参数:
	$columns (array) -- 查询列
返回:
	查询记录
返回类型:
	object

`paginate([ $perPage = null, $columns = [], $pageName = 'page', $page = null ])`
参数:
	$perPage (int) -- 每页行数
	$columns (array) -- 查询列
	$pageName (array) -- 页码字段键名
	$page (int) -- 页码
返回:
	查询记录
返回类型:
	object

`sole([ $columns = [] ])`
参数:
	$columns (array) -- 查询列
返回:
	查询唯一记录
返回类型:
	object

`first([ $columns = [] ])`
参数:
	$columns (array) -- 查询列
返回:
	查询第一条记录
返回类型:
	object

`exists()`
返回:
	记录 是否存在
返回类型:
	bool

`max($column)`
参数:
	$column (string) -- 查询列
返回:
	最大值
返回类型:
	mixed

`update($value)`
参数:
	$value (array) -- 更新数据
返回:
	更新行数
返回类型:
	int

`forceDelete()`
返回:
	删除行数
返回类型:
	int

`destroy()`
返回:
	删除行数
返回类型:
	int
