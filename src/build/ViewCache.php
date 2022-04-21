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
use willphp\route\Route;
use willphp\config\Config;
use willphp\cache\Cache;
/**
 * 模板缓存
 * Trait ViewCache
 * @package willphp\view\build;
 */
trait ViewCache {
	protected $expire = 0; //缓存时间		
	/**
	 * 设置缓存时间
	 * @param int $expire 缓存时间
	 * @return $this
	 */
	public function cache($expire = 0) {
		$this->expire = max(0, intval($expire));	
		return $this;
	}
	/**
	 * 设置模板缓存
	 * @param $content
	 * @return mixed
	 */
	public function setCache($content) {	
		$expire = ($this->expire > 0)? $this->expire : Config::get('view.cache_time', 0);
		return $this->viewCache()->set($this->cacheName(), $content, $expire);
	}
	/**
	 * 检测模板缓存
	 * @param $content
	 * @return mixed
	 */
	public function hasCache() {		
		return $this->getCache() ? true : false;
	}
	/**
	 * 获取模板缓存
	 * @return mixed
	 */
	public function getCache() {		
		return $this->viewCache()->get($this->cacheName());
	}
	/**
	 * 删除模板缓存
	 * @return mixed
	 */
	public function delCache($route = '') {
		if (is_array($route)) {
			foreach ($route as $v) {
				$name = $this->cacheName($v);
				$this->viewCache()->del($name);
			}
			return true;
		}
		$name = $this->cacheName($route);		
		return $this->viewCache()->del($name);
	}
	/**
	 * 缓存驱动
	 * @return string
	 */
	protected function viewCache() {
		return Cache::driver('file')->dir(Config::get('view.cache_dir'));
	}
	/**
	 * 缓存标识
	 * @return string
	 */
	protected function cacheName($route = '') {
		return md5(Route::getRoute($route));
	}
}