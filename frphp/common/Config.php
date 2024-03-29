<?php

// +----------------------------------------------------------------------
// | frphp { a friendly PHP Framework }
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://frphp.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2018/02
// +----------------------------------------------------------------------



/**
 * 核心公共配置
 */
 

defined('APP_PATH') or exit();

return array(
	/*系统设定*/
	'Tpl_style'			=>	 APP_PATH.'public',//公共静态文件
	'Tpl_common'   		=>   'common',//模板公共目录Home\View\common
	'APP_HOME'     		=>   'app',//默认前台目录
	'APP_URL'     		=>   APP_PATH.'index.php',//默认前台入口
	'HOME_CONTROLLER'   =>   'Controller',//默认控制器文件目录
	'HOME_MODEL'   		=>   'Model',//默认模型文件目录
	'HOME_VIEW'     	=>   'View',//默认模板文件目录
	'PC_VIEW'        	=>   'pc',//默认PC模板目录
	'MOBILE_VIEW'       =>   'wap',//默认手机模板目录
	'Tpl_template'		=>	 '',//默认模板目录-二级目录-多端口配置
	'OPEN_MOBILE'	    =>	 true,//默认开启手机端
	'File_TXT'    		=>	 '.html',//默认模板后缀名
	'SessionTime'		=>	 3600,//默认缓存时间
	'APP_DEBUG'			=>	 false,//关闭调试
	'StopLog'			=>	 false,//关闭事件日志
	'DefaultController'	=>	 'Index',//默认控制器
	'DefaultAction'		=>	 'index',//默认方法
	'open_url_route'	=>	 true,//开启自定义路由
	'open_redis_session'=>	 false,//开启redis缓存session
	'Cache_Path'		=>	 APP_PATH.'cache',//缓存目录
	'Session_Path'		=>	 APP_PATH.'cache/tmp',//session存储目录
	'APP_LANG'			=>	 'zh_cn',//默认当前语言，zh_cn中文简体，其他自定义
	'APP_LANG_REQUREST'	=>	 'l',//语言包接收参数（小写的L）
	'CONF_PATH'      	=>	 'conf',//配置文件夹
    'ROOT'				=>	 '/',//根目录路径
    'APP_MUTI'			=>	 false,//多模块标记
    'APP_MODEL'			=>	 'Home',//当前模块标识






);