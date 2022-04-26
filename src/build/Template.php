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
/**
 * 模板引擎
 * Class Template
 * @package willphp\view\build
 */
class Template {	
	/**
	 * 编译模板
	 * @return $this
	 */
	public static function compile($content, $vars = []) {
		$content = self::parseInclude($content);		
		$var_name = '([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)'; //匹配变量名
		$key_name = '([a-zA-Z0-9_\x7f-\xff]*)'; //匹配键名
		$pattern = [
				'/\{\s*\$'.$var_name.'\s*\}/i', //$变量名		
				'/\{\s*\$'.$var_name.'\.'.$key_name.'\s*\}/i', //$变量名.键名
				'/\{\s*\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\s*\}/i', //$变量名[键名]				
				'/\{\s*\$'.$var_name.'\[\$'.$var_name.'\]\s*\}/i', //$变量名[$变量名]  20220425		
				'/\{\s*\$'.$var_name.'\[\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\]\s*\}/i', //$变量名[$变量名[键名]]  20220425				
				'/\{\s*\$'.$var_name.'\.'.$key_name.'\.'.$key_name.'\s*\}/i', //$变量名.键名.键名
				'/\{\s*\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\[[\'"]?'.$key_name.'[\'"]?\]\s*\}/i', //$变量名[键名][键名]
				'/\{\s*\$'.$var_name.'\.'.$key_name.'\.'.$key_name.'\.'.$key_name.'\s*\}/i', //$变量名.键名.键名.键名
				'/\{\s*\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\[[\'"]?'.$key_name.'[\'"]?\]\[[\'"]?'.$key_name.'[\'"]?\]\s*\}/i', //$变量名[键名][键名][键名]
				'/\{\s*var\s+\$'.$var_name.'\s*=\s*(.+?)\s*\}/i', //var $变量名=*
				'/\{\s*:'.$var_name.'\((.*?)\)\s*\}/i', //: 函数名称(*)
				'/\{\s*if\s*(.+?)\s*\}/i', // if (*)
				'/\{\s*\/(if|foreach)\s*\}/i', // end if | end foreach
				'/\{\s*else\s*\}/i', //else
				'/\{\s*(else if|elseif)\s*(.+?)\s*\}/i', //else if (*)
				'/\{\s*foreach\s+\$'.$var_name.'\s+as\s+\$'.$var_name.'\s*\}/i', //foreach $数组 as $变量
				'/\{\s*foreach\s+\$'.$var_name.'\s+as\s+\$'.$var_name.'\s*=>\s*\$'.$var_name.'\s*\}/i', //foreach $数组 as $键名=>$键值
				'/\{\s*empty\s*(.+?)\s*\}/i', // foreach 中 empty $数组
				'/\{\s*\$'.$var_name.'\|'.$var_name.'\s*\}/i', //$变量名|函数
				'/\{\s*\$'.$var_name.'\|'.$var_name.'=(.+?)\s*\}/i', //$变量名|函数=参数	
				'/\{\s*:'.$var_name.'\((.*?)\)\->'.$var_name.'\((.*?)\)\s*\}/i', //:函数()->方法()
				'/\{\s*\$'.$var_name.'\->'.$var_name.'\((.*?)\)\s*\}/i', //$对象名->方法()
		]; //正则
		$replace = [
				'<?php echo $\\1; ?>',
				'<?php echo $\\1[\'\\2\']; ?>',
				'<?php echo $\\1[\'\\2\']; ?>',
				'<?php echo $\\1[$\\2]; ?>',
				'<?php echo $\\1[$\\2[\'\\3\']]; ?>',				
				'<?php echo $\\1[\'\\2\'][\'\\3\']; ?>',
				'<?php echo $\\1[\'\\2\'][\'\\3\']; ?>',
				'<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\']; ?>',
				'<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\']; ?>',				
				'<?php $\\1 = \\2; ?>',
				'<?php echo \\1(\\2); ?>',
				'<?php if (\\1) { ?>',
				'<?php } ?>',
				'<?php } else { ?>',
				'<?php } elseif (\\2) { ?>',
				'<?php foreach($\\1 as $\\2) { ?>',
				'<?php foreach($\\1 as $\\2 => $\\3) { ?>',
				'<?php } if (empty(\\1)) { ?>',
				'<?php echo \\2($\\1); ?>',
				'<?php echo \\2($\\1,\\3); ?>',
				'<?php echo \\1(\\2)->\\3(\\4); ?>',
				'<?php echo $\\1->\\2(\\3); ?>',
			
		]; //替换
		$content = preg_replace($pattern, $replace, $content);			
		return $content;
	}
	/**
	 * 文件包含处理
	 * @return $this
	 */
	protected static function parseInclude($content) {
		$content = preg_replace_callback('/\{\s*include\s+[\"\']?(.+?)[\"\']?\s*\}/i', function ($match) {
			return is_file(THEME_PATH.'/'.$match[1])? file_get_contents(THEME_PATH.'/'.$match[1]) : '['.$match[1].']';
		}, $content);		
		if (preg_match('/\{\s*include\s+[\"\']?(.+?)[\"\']?\s*\}/i', $content)) {
			return self::parseInclude($content);
		}
		return $content;
	}
}