<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: no-mind <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\view\build;
use willphp\config\Config;
use willphp\route\Route;
/**
 * 模板处理
 * Class Base
 * @package willphp\view\build;
 */
class Base {
	use ViewCache;
	protected static $vars = []; //模板变量集合
	protected $viewfile; //模板文件
	protected $compilefile; //编译文件	
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
	 * 返回模板解析后的内容
	 * @param string $file
	 * @param array  $vars
	 * @return string
	 */
	public function fetch($file = '', $vars = []) {				
		return $this->make($file, $vars)->parse();
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
		if (Config::get('view.view_cache') && ($cache = $this->getCache())) {
			return $cache;
		}		
		$res = $this->parse();	
		if (Config::get('view.view_cache')) {			
			$this->setCache($res);
		}		
		return $res;
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
	 * 设置模板文件
	 * @param $file 模板文件
	 * @return string|void
	 */
	public function setFile($file = '') {
		$file = $this->parseFile($file);		
		$theme = THEME_ON ? Config::get('site.theme', 'default') : ''; 
		define('THEME_PATH', THEME_ON ? VIEW_PATH.'/'.$theme : VIEW_PATH);
		$viewfile = THEME_PATH.'/'.$file;		
		if (!file_exists($viewfile) && THEME_ON && $theme != 'default') {
			$viewfile = VIEW_PATH.'/default/'.$file;
		}
		if (!file_exists($viewfile)) {
			$theme = $theme? '['.$theme.']' : '';
			throw new \Exception($theme.$file.'模板文件不存在。');
		}
		$this->viewfile = $viewfile;
		$compile_dir = Config::get('view.compile_dir');
		$this->compilefile = $compile_dir.'/'.preg_replace('/[^\w]/', '_', $file).'_'.substr(md5($file), 0, 5).'.php';
		return $this;
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
	 * 获取所有分配变量
	 * @return array
	 */
	public function getVars() {
		return self::$vars;
	}
	/**
	 * 解析处理
	 * @return string
	 */
	protected function parse() {
		$this->compile();
		ob_start();
		extract(self::$vars);
		include $this->compilefile;
		return ob_get_clean();
	}
	/**
	 * 模板编译
	 * @return $this
	 */
	protected function compile() {
		$status = Config::get('app.debug') || !is_file($this->compilefile) || (filemtime($this->viewfile) > filemtime($this->compilefile));
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
		if (Config::get('route.csrf_check')) {
			$content = preg_replace('#(<form.*>)#', '$1'.PHP_EOL.'<?php echo csrf_field();?>', $content);
		}
		return $content;
	}
}