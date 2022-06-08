##视图处理

view组件用于处理框架视图

###安装组件

使用 composer命令进行安装或下载源代码使用(依赖config,route,cache,session组件)。

    composer require willphp/view

> WillPHP框架已经内置此组件，无需再安装。

###视图配置

`config/view.php`配置文件可设置：
	
	'left_delimiter' => '\{', //模板左标识符
	'right_delimiter' => '\}', //模板右标识符
	'prefix' => '.html', //模板文件后缀
	'view_cache' => false, //是否开启模板缓存
	'cache_time' => 10, //缓存时间(0,永久)秒
	'compile_dir' => RUNTIME_PATH.'/view', //模板编译路径
	'csrf_check' => true, //是否开启csrf表单令牌验证

###模板配置

如在 app/home/config/site.php 设置：

    'view_path' => ROOT_PATH.'/template', //模板文件路径
    'theme_on' => true, //多主题开关
    'theme' => 'blue', //当前主题

###赋值与渲染

助手函数：

    //赋值
    view_with([变量名=>变量值]); //或view_with(变量名,变量值)
    //渲染
    view('[模板文件]', [变量名=>变量值]);
    //更新视图缓存
    view_update(['控制器/方法']); //当使用视图缓存时

示例代码：

    namespace app\home\controller;
    class Index{
        public function index() {
            return 'index';
        }   
        public function test0(){
            view_with([
                'hi' => 'test0',
                'msg' => 'php',
            ]);     
            return view(); //默认渲染index/test0.html
        }
        public function test1(){                
            return view('index/test0', ['hi' => 'test1','msg' => 'php']);
        }
        public function test2() {
            return view('test0')->with(['hi' => 'test2','msg' => 'php']);
        }
        public function test3() {       
            view_with('hi', 'test3');
            view_with('msg', 'php');        
            return view('test0')->cache(30); //缓存30秒
        }       
        public function update3() {
            $r = view_update('index/test3');
            echo '更新成功';
        }
    }

对应模板文件 app/home/view/index/test0.html 代码：

    <h1>{$hi}, {$msg}</h1>
    <p>{:date('Y-m-d H:i:s')}</p>

访问 http://您的域名/index.php/index/test0(0~3) 结果：

    <h1>test[0~3], php</h1>
    <p>xxxx-xx-xx xx:xx:xx</p> //test0~2每刷新变一次，test3刷新30秒变一次

访问 http://您的域名/index.php/index/update3 后直接更新test3的视图缓存。

###视图缓存

可以在config/view.php中配置缓存所有视图：

    'view_cache' => true, //是否开启视图缓存
    'cache_time' => 0, //缓存时间(0,永久)秒      

更新多个视图缓存，如：

    view_update(['index/test3','index/test2']);     

###csrf保护

当设置csrfcheck=true时，将自动对csrftoken进行验证。

表单令牌：

模板渲染时会对<form>表单添加csrf_token字段(自动添加)：

    <input type='hidden' name='csrf_token' value='8aa54a330fa48a36ae2a6e9917afb4e8'/>

Ajax提交也需要转入csrf_token字段(手动设置)：

    <script>
    $.ajax({
       type: "POST",
       url: "{:url('index/test')}",
       data:"csrf_token={:csrf_token()}&name=willphp",
       success: function(msg){
         alert(msg);
       }
    });
    </script>

也可以设置请求头：

    <meta name="csrf-token" content="{:csrf_token()}">  
    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }); 
    </script>
 
##模板语法   
    
###常量输出

以常量名格式的常量，可在模板中直接输出，如：

    __ROOT__    根路径
    __STATIC__  静态资源
    __UPLOAD__  上传路径
    __HOST__    主机名
    __HISTORY__ 来源url
    __THEME__   当前主题
    __WEB__     基url
    __URL__     基url(含域名)

示例代码：

    <script src="__STATIC__/js/js.js"></script>
    <a href="__ROOT__/admin.php">管理</a>
    <a href="__URL__/about/index">关于</a>        

###模板包含

    {include 'public/header.html'}

###变量输出

    {$vo['id']} 
    {$vo.id} 
    {$list.0.id} 
    {$cate[$vo['cid']]}

###变量定义

    {var $ctime=time()} 

###函数处理

    {:date('Y-m-d H:i:s', $ctime)}  
    {$cid|intval}

###条件语句

    {if $arr['id']==1}id=1{/if}
    {if $var==1}yes{else}no{/if}

###循环语句

    //一维数组
    {foreach $cates as $cid=>$cname}
        {$cid}--{$cname}
    {/foreach}  
    //二维数组
    {foreach $list as $vo}
        {$vo.id}--{$vo.name}
    {empty $list}
        没有记录
    {/foreach}    
     