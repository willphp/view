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
use willphp\route\Route;
/**
 * 跳转信息
 * Trait Jump
 * @package willphp\jump
 */
trait Jump {
	protected $codes = [200=>'请求成功',204=>'暂无记录',400=>'未知错误',401=>'请先登录',403=>'表单令牌验证失败',404=>'页面未找到',500=>'服务器内部错误'];
	protected $isApi = false; //是否api模式	
	/**
	 * 输出josn数据
	 * @param int $code 状态码
	 * @param string $msg 提示信息
	 * @param array $data json数据
	 * @param string $url 跳转URL
	 */
	protected function json($code = 200, $msg = '', $data = [], $url = null) {
		header('Content-type: application/json;charset=utf-8');
		if (empty($msg) && isset($this->codes[$code])) {
			$msg = $this->codes[$code];
		}
		$status = ($code < 400)? 1 : 0;
		$url = is_null($url)? '' : Route::buildUrl($url);	
		$res = ['code' => $code, 'status'=>$status, 'msg' => $msg, 'data' => $data, 'url'=> $url];
		exit(json_encode($res, JSON_UNESCAPED_UNICODE));
	}	
	/**
	 * 成功跳转
	 * @param mixed $msg 提示信息
	 * @param string $url 跳转URL
	 */
	protected function success($msg = '', $url = null) {
		if (empty($msg)) {
			$msg = $this->codes[200];
<<<<<<< HEAD
		}		
		if ($this->isApi || $this->isAjax()) {
			$this->json(200, $msg, [], $url);
		}
		$url = is_null($url)? '' : Route::buildUrl($url);
=======
		}			
		$url = is_null($url)? '' : Route::buildUrl($url);	
		if ($this->isApi || $this->isAjax()) {
			$this->json(200, $msg, [], $url);
		}		
>>>>>>> 374112219767831c48244a3915ce4e325702f7aa
		$res = ['code' => 200, 'status' => 1, 'msg' => $msg, 'url' => $url];
		echo View::fetch('public:jump', $res);
		exit();
	}
	/**
	 * 错误跳转
	 * @param mixed $msg 提示信息
	 * @param string $url 跳转URL
	 */
	protected function error($msg = '', $url = null) {
		if (empty($msg)) {
			$msg = $this->codes[400];
		}		
<<<<<<< HEAD
		$url = is_null($url)? 'javascript:history.back(-1);' : $url;			
		if ($this->isApi || $this->isAjax()) {
			$this->json(400, $msg, [], $url);
		}	
		$url = Route::buildUrl($url);
=======
		$url = is_null($url)? 'javascript:history.back(-1);' : Route::buildUrl($url);
		if ($this->isApi || $this->isAjax()) {
			$this->json(400, $msg, [], $url);
		}		
>>>>>>> 374112219767831c48244a3915ce4e325702f7aa
		$res = ['code' => 400, 'status' => 0, 'msg' => $msg, 'url' => $url];
		echo View::fetch('public:jump', $res);
		exit;
	}	
	/**
	 * 页面未找到
	 */
	protected function _404() {
		$route = Route::getRoute();
		$this->error($route.' 不存在！');
	}
	/**
	 * 跳转合并
	 * @param mixed $info 提示信息
	 * @param number $status 操作结果状态
	 * @param string $url 跳转的URL地址
	 */
	protected function _jump($info, $status = 0, $url = null) {		
		$msg = [];
		if (is_array($info)) {
			$msg = $info;
		} else {
			$msg[0] = $msg[1] = $info;
		}
		if ($status) {
			$this->success($msg[0], $url);
		} else {
			$this->error($msg[1], $url);
		}
	}
	/**
	 * URL重定向
	 * @param string $url 跳转URL
	 * @param int $time 跳转时间
	 */
	protected function _url($url, $time = 0) {
		$url = Route::buildUrl($url);
		$time = max(0, intval($time));
		if ($time == 0) {
			header('Location:'.$url);
		} else {
			header("refresh:{$time};url={$url}");
		}
		exit();
<<<<<<< HEAD
	}	
=======
	}
>>>>>>> 374112219767831c48244a3915ce4e325702f7aa
	/**
	 * 是否Ajax提交
	 * @return bool
	 */
	protected function isAjax(){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			return true;
		}
		return $this->isApi;
	}
	/**
	 * 是否POST提交
	 * @return bool
	 */
	protected function isPost() {
		return ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || preg_replace("~https?:\/\/([^\:\/]+).*~i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("~([^\:]+).*~", "\\1", $_SERVER['HTTP_HOST']))) ? true : false;
	}
}