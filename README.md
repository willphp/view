# 视图处理
view组件用于处理php框架视图

#开始使用

####安装组件

使用 composer 命令进行安装或下载源代码使用。

    composer require willphp/view

> WillPHP 框架已经内置此组件，无需再安装。

####使用示例

    $res = \willphp\view\View::make('index/index', ['name'=>'willphp']);

####视图配置

`config/view.php`配置文件可设置：
	
	'prefix' => '.html', //模板文件后缀
	'compile_dir' => RUNTIME_PATH.'/view/compile', //模板编译路径
	'view_cache' => true, //是否开启模板缓存
	'cache_time' => 0, //缓存时间(0,永久)
	'cache_dir' => RUNTIME_PATH.'/view/cache', //模板缓存路径
	'csrf_check' => true, //是否开启csrf表单令牌验证

####常量设置

	define('THEME_ON', false); //多主题(默认关闭)
	define('APP_PATH', WIPHP_URI.'/app/'.APP_NAME); //当前应用路径
	define('VIEW_PATH', APP_PATH.'/view'); //模板路径
	define('RUNTIME_PATH', WIPHP_URI.'/runtime/'.APP_NAME); //运行编译目录

####分配变量

	View::with('name', 'willphp');
	View::with(['id'=>1, 'name'=>'test']);

####显示模板

	return View::make('index/index', ['name'=>'test']);

####缓存模板

	return View::cache(100)->make(); //缓存100秒

####返回内容

	$res = View::fetch('index/index', ['name'=>'test']);

####助手函数

	return view()->with('id', 1);

#模板引擎

####特定常量

    __STATIC__      ：静态资源目录 
    __URL__         ：网站基础URL
    __ROOT__        ：网站根目录

####包含模板

    包含模板：{include 'public/header.html'} 

####变量定义

    定义变量：{var $ctime=time()} 

####变量与数组

    变量：{$var} 数组：{$arr.id}，{$arr.0.0}或{$arr['id']}

####函数处理

    执行intval：{$cid|intval} 
    获取当前IP：{:get_ip()}
    网站标题：{:config('site_title')}
    生成url：{:url('index/index')}
    格式化时间：{:date('Y-m-d H:i', $ctime)}

####条件判断

    {if $arr['id']==1}id=1{/if} 或 {if $var==1}yes{else}no{/if}

####数组循环

    {foreach $list as $vo}
        {$vo.id}--{$vo.name}
    {empty $list}
    没有记录
    {/foreach}
    或：
    {foreach $list as $k=>$vo}
        {$k}--{$vo.id}
    {/foreach}

