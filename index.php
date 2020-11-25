<?php

// +----------------------------------------------------------------------
// | FrPHP { a friendly PHP Framework } 
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://frphp.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/02
// +----------------------------------------------------------------------



// 应用目录为当前目录
define('APP_PATH', __DIR__ . '/');

//定义项目目录
define('APP_HOME','app');

//定义项目模板文件目录-根目录template
define('HOME_VIEW',APP_PATH.'static');

//定义项目模板公共文件目录
define('Tpl_common','');

//定义项目控制器文件目录
define('HOME_CONTROLLER','c');

//定义项目模型文件目录
define('HOME_MODEL','m');

//定义项目默认方法
define('DefaultController','Index');

//定义项目默认方法
define('DefaultAction','Index');

//取消log
define('StopLog',false);


//定义静态文件路径
define('Tpl_style',APP_PATH.'static/');

// 加载框架文件
require(APP_PATH . 'FrPHP/Fr.php');

// 就这么简单~

