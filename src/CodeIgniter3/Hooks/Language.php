<?php

// 命名空间
namespace Xzb\CodeIgniter3\Hooks;

/**
 * 语言
 */
class Language
{

	/**
	 * 解析
	 * 
	 * @return void
	 */
	public static function boot()
	{
		// 非命令行时, 解析 请求语言
		if (! is_cli()) {
			// 获取 请求语言, 不存在时, 使用默认
			$languages = self::getRequestLanguages() ?: self::getDefaultLanguages();
			
			// 获取 语言信息
			if ($language = self::getLanguage($languages)) {
				// 设置 语言
            	self::setLanguage($language);
			}
		}
	}

	/**
	 * 获取 请求语言
	 * 
	 * @return string
	 */
	protected static function getRequestLanguages()
	{
		// 语言 集合
		$languages = [];

		// 加载 输入类
		$input =& load_class('Input', 'core');

		// 请求头中 Accept-Language
		// $headerAcceptLanguage = $input->get_request_header('Accept-Language', $xss_clean = TRUE);
		// $headerAcceptLanguage = get_instance()->input->get_request_header('Accept-Language', $xss_clean = TRUE);
		// $acceptLanguage = $input->server('HTTP_ACCEPT_LANGUAGE');
		$acceptLanguage = $input->server('HTTP_ACCEPT_LANGUAGE')
					?: $input->get_request_header('Accept-Language');

		/**
		 * 语种代码标准
		 *
		 * ISO 639 语种代码，两个字母表示一个语种，比如：zh表示中文、en表示英文
		 *
		 * ISO 3166 国家地区代码，比如：CN是CHina简称、US是United States of America简称
		 *
		 * RFC 1766 一个组合方案，把 语种代码 和 国家地区代码 拼接，表示不同的国家地区使用的语种
		 * 			比如：zh-CN表示中国大陆的中文、zh-TW表示台湾地区的中文、zh-HK表示香港地区的中文
		 *
		 * RFC4646 另一种组合方案，语种代码、子语种 和 国家地区代码 拼接，不过一般不用第三部分
		 * 			比如：zh-Hans表示简体中文、zh-Hans-HK表示香港简体中文
		 *
		 *
		 * 按国家划分可以使用 ISO 3166
		 * 按语种划分可以使用 ISO 639
		 * 具体到简体和繁体，RFC 1766 和 RFC4646 两个都可以
		 * 只是想表示语种而不想纠结地区 RFC4646 会更加合适
		 */

		// 解析 需要安装国际化扩展(intl)
		if ($language = locale_accept_from_http($acceptLanguage)) {
			// 添加到集合
			array_push($languages, $language);
			// 主要语言 添加到集合
			array_push(
				$languages,
				// 主语言
				locale_get_primary_language($language)
			);
		}

		// 去除重复
		return array_unique($languages);
	}

	/**
	 * 获取 默认语言
	 * 
	 * @return string
	 */
	protected static function getDefaultLanguages()
	{
		return [
			'locale'			=> 'zh',
			'fallback_locale'	=> 'en',
		];
	}

	/**
	 * 获取 语言
	 * 
	 * @return array
	 */
	protected static function getLanguage($languages)
	{
		// 获取 支持语言列表
		$availableLocales = self::getAvailableLocales();

		// 获取 交集语言
		$intersectLanguages = array_intersect_key(
			array_column($availableLocales, null, 'abbr'),
			$languages = array_fill_keys($languages, null)
		);

		$languages = array_filter(array_merge($languages, $intersectLanguages));

		return reset($languages);
	}

	/**
	 * 获取 可用语言列表
	 * 
	 * @return array
	 */
	protected static function getAvailableLocales()
	{
		return [
			[
				'title' => '英文', 'en_title' => 'English', 'locale_title' => 'English', 'abbr' => 'en',
				'ci_package' => 'english'
			],
			[
				'title' => '中文', 'en_title' => 'Chinese', 'locale_title' => '中文', 'abbr' => 'zh',
				'ci_package' => 'simplified-chinese'
			],
		];
	}

	/**
	 * 设置 语言
	 * 
	 * @param array $language
	 * @return void
	 */
	protected static function setLanguage(array $language)
	{
		// 加载 配置类
		$config =& load_class('Config', 'core');

		// 将 语言信息 设置在全局配置中
		$config->set_item('language', $language['ci_package']);
		$config->set_item('locale', $language['abbr']);
	}

}
