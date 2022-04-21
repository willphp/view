<?php
if (!function_exists('view')) {
	/**
	 * 显示模板
	 * @param string $file 模板文件
	 * @param array $vars 变量数组
	 * @return $this
	 */
	function view($file = '', $vars = []) {
		return \willphp\view\View::make($file, $vars);
	}
}
if (!function_exists('view_update')) {
	/**
	 * 更新模板缓存
	 * @param string|array $route 模板路由
	 * @return bool
	 */
	function view_update($route = '') {
		return \willphp\view\View::delCache($route);
	}
}
if (!function_exists('csrf_field')) {
	/**
	 * CSRF 表单
	 * @return string
	 */
	function csrf_field() {
		return "<input type='hidden' name='csrf_token' value='".csrf_token('csrf_token')."'/>\r\n";
	}
}
if (!function_exists('csrf_token')) {
	/**
	 * CSRF 值
	 * @return mixed
	 */
	function csrf_token() {
		return \willphp\session\Session::get('csrf_token');
	}
}