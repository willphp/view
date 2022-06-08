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
/**
 * 跳转信息
 * Trait Jump
 * @package willphp\jump
 */
trait Jump {
	protected $codes = [200=>'请求成功',204=>'暂无记录',400=>'未知错误',401=>'请先登录',403=>'表单令牌验证失败',404=>'页面未找到',500=>'服务器内部错误'];
	protected $isApi = false; //是否api模式
	/**
	 * 显示json信息
	 * @param number $code
	 * @param string $msg
	 * @param array $data
	 * @param array $extend
	 */
	protected function json($code = 200, $msg = '', $data = null, $extend = []) {
		if (empty($msg) && isset($this->codes[$code])) {
			$msg = $this->codes[$code];
		}
		$this->showJson($code, $msg, $data, $extend);
	}
	//json统一显示
	protected function showJson($code, $msg = '', $data = null, $extend = []) {
		header('Content-type: application/json;charset=utf-8');
		$json = Config::get('json', ['ret'=>'ret','msg'=>'msg','data'=>'data', 'status'=>'status']);
		$res = [];
		$res[$json['ret']] = $code;
		$res[$json['msg']] = $msg;
		if (null !== $data) {
			$res[$json['data']] = $data;
		}
		$res[$json['status']] = ($code < 400)? 1 : 0;
		$res = array_merge($res, $extend);
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
		}
		if (is_array($msg)) {
			$msg = current($msg);
		}
		$url = is_null($url)? '' : Route::buildUrl($url);
		if ($this->isApi || IS_AJAX) {
			$this->json(200, $msg, null, ['url'=>$url]);
		}
		$res = ['status' => 1, 'msg' => $msg, 'url' => $url];
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
		if (is_array($msg)) {
			$msg = current($msg);
		}
		$url = is_null($url)? 'javascript:history.back(-1);' : Route::buildUrl($url);
		if ($this->isApi || IS_AJAX) {
			$this->json(400, $msg, null, ['url'=>$url]);
		}
		$res = ['status' => 0, 'msg' => $msg, 'url' => $url];
		echo View::fetch('public:jump', $res);
		exit;
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
	}
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
		return ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || preg_replace("~https?:\/\/([^\:\/]+).*~i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("~([^\:]+).*~", "\\1", $_SERVER['HTTP_HOST'])));
	}
	/**
	 * 是否GET提交
	 * @return bool
	 */
	protected function isGet() {
		return $_SERVER['REQUEST_METHOD'] == 'GET';
	}
	/**
	 * 是否PUT提交
	 * @return bool
	 */
	protected function isPut() {
		return $_SERVER['REQUEST_METHOD'] == 'PUT' || (isset($_POST['_method']) && $_POST['_method'] == 'PUT');
	}
	/**
	 * 是否DELETE提交
	 * @return bool
	 */
	protected function isDelete() {
		return $_SERVER['REQUEST_METHOD'] == 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] == 'DELETE');
	}
}