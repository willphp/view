<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: no-mind <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\view;
use willphp\config\Config;
use willphp\route\Route;
use willphp\cache\Cache;
class View {
	protected static $link;
	public static function single()	{
		if (!self::$link) {
			self::$link = new ViewBuilder();
		}
		return self::$link;
	}
	public function __call($method, $params) {
		return call_user_func_array([self::single(), $method], $params);
	}
	public static function __callStatic($name, $arguments) {
		return call_user_func_array([self::single(), $name], $arguments);
	}
}
class ViewBuilder {
	protected static $vars = []; //模板变量集合
	protected $viewfile; //模板文件
	protected $compilefile; //编译文件
	protected $hash; //页面标识
	protected $expire = false; //模板缓存时间(false不缓存，小于2，永久，大于1限时)
	/**
	 * 构造
	 */
	public function __construct() {
		$isCache = Config::get('view.view_cache', false); //是否开启缓存
		if ($isCache) {
			$this->expire = Config::get('view.cache_time', 0);
		}
		$this->hash = $this->getHash();
	}
	/**
	 * 获取页面标识
	 * @param string $route
	 * @return string
	 */
	public function getHash($route = '') {
		$path = Route::getPath($route);
		return 'view.'.md5($path);
	}
	/**
	 * 更新模板缓存
	 * @return mixed
	 */
	public function updateCache($route = '') {
		if (is_array($route)) {
			foreach ($route as $v) {
				$name = $this->getHash($v);
				Cache::del($name);
			}
			return true;
		}
		$name = $this->getHash($route);
		return Cache::del($name);
	}
	/**
	 * 获取所有分配变量
	 * @return array
	 */
	public function getVars() {
		return self::$vars;
	}
	/**
	 * 解析模板
	 * @param string $file 模板文件
	 * @param mixed  $vars 分配的变量
	 * @return $this
	 */
	public function make($file = '', $vars = []) {
		$this->setFile($file);
		$this->with($vars);
		return $this;
	}
	/**
	 * 设置模板文件
	 * @param $file 模板文件
	 * @return string|void
	 */
	public function setFile($file = '') {
		$file = $this->parseFile($file);
		$viewfile = THEME_PATH.'/'.$file;
		if (!file_exists($viewfile) && THEME_ON && __THEME__ != 'default') {
			$viewfile = VIEW_PATH.'/default/'.$file;
		}
		if (!file_exists($viewfile)) {
			$theme = __THEME__? '['.__THEME__.']' : '';
			throw new \Exception($theme.$file.'模板文件不存在。');
		}
		$this->viewfile = $viewfile;
		$compile_dir = Config::get('view.compile_dir');
		$this->compilefile = $compile_dir.'/'.preg_replace('/[^\w]/', '_', $file).'_'.substr(md5($file), 0, 5).'.php';
		return $this;
	}
	/**
	 * 处理模板文件
	 * @param $file 模板文件
	 * @return string
	 */
	protected function parseFile($file = '') {
		$path = Route::getController();
		if (empty($file)) {
			$file = Route::getAction();
		} elseif (strpos($file, ':')) {
			list($path, $file) = explode(':', $file);
		} elseif (strpos($file, '/')) {
			$path = '';
		}
		$file = trim($path.'/'.$file, '/');
		if (!preg_match('/\.[a-z]+$/i', $file)) {
			$file .= Config::get('view.prefix');
		}
		return $file;
	}
	/**
	 * 返回模板解析后的内容
	 * @param string $file
	 * @param array  $vars
	 * @return string
	 */
	public function fetch($file = '', $vars = []) {
		return $this->make($file, $vars)->parse();
	}
	/**
	 * 分配变量
	 * @param mixed  $vars  变量名
	 * @param string $value 值
	 * @return $this
	 */
	public function with($vars, $value = '') {
		if (!is_array($vars)) {
			$this->set($vars, $value);
		} else {
			foreach ($vars as $k => $v) {
				$this->set($k, $v);
			}
		}
		return $this;
	}
	/**
	 * 设置变量
	 * @param mixed  $vars  变量名
	 * @param string $value 值
	 * @return bool
	 */
	protected function set($vars, $value) {
		$temp = &self::$vars;
		$all = explode('.', $vars);
		foreach ((array)$all as $v) {
			if (!isset($temp[$v])) {
				$temp[$v] = [];
			}
			$temp = &$temp[$v];
		}
		$temp = $value;
		return true;
	}
	/**
	 * 设置缓存时间
	 * @param mixed $expire 缓存时间
	 * @return $this
	 */
	public function cache($expire = true) {
		if (false !== $expire && intval($expire) > 0) {
			$expire = intval($expire);
			$this->expire = ($expire > 1)? $expire : 0;
		}
		return $this;
	}
	/**
	 * 获取模板缓存
	 * @return mixed
	 */
	public function getCache() {
		return Cache::get($this->hash);
	}
	/**
	 * 设置模板缓存
	 * @param $content
	 * @return mixed
	 */
	public function setCache($content) {
		return Cache::set($this->hash, $content, $this->expire);
	}
	/**
	 * 显示模板对象
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}
	/**
	 * 显示模板
	 * @return string
	 */
	public function toString() {
		if (false !== $this->expire && ($cache = $this->getCache())) {
			return $cache;
		}
		$res = $this->parse();
		if (false !== $this->expire && $this->expire >= 0) {
			$this->setCache($res);
		}
		return $res;
	}
	/**
	 * 解析处理
	 * @return string
	 */
	protected function parse() {
		$this->compile();
		ob_start();
		array_walk_recursive(self::$vars, 'self::parseVars');
		extract(self::$vars);
		include $this->compilefile;
		return ob_get_clean();
	}
	/**
	 * 输出前处理数据
	 * @param string $value
	 * @param string $key
	 */
	protected static function parseVars(&$value, $key) {
		if (is_scalar($value) && !is_numeric($value)) {
			$value = htmlspecialchars($value);
			$value = stripslashes($value);
		}
	}
	/**
	 * 模板编译
	 * @return $this
	 */
	protected function compile() {
		$status = APP_DEBUG || !is_file($this->compilefile) || (filemtime($this->viewfile) > filemtime($this->compilefile));
		if ($status) {
			is_dir(dirname($this->compilefile)) or mkdir(dirname($this->compilefile), 0755, true);
			$content = file_get_contents($this->viewfile);
			$content = Template::compile($content, self::$vars);
			$content = $this->csrf($content);
			file_put_contents($this->compilefile, $content);
		}
		return $this;
	}
	/**
	 * 添加表单令牌到内容
	 * @param string $content 内容
	 * @return string
	 */
	protected function csrf($content) {
		if (Config::get('view.csrf_check')) {
			$content = preg_replace('#(<form.*>)#', '$1'.PHP_EOL.'<?php echo csrf_field();?>', $content);
		}
		return $content;
	}
}