<?php

// +----------------------------------------------------------------------
// | frphp { a friendly PHP Framework }
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://frphp.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2022/02/28
// +----------------------------------------------------------------------


namespace app\c;
use frphp\lib\Controller;

class IndexController extends Controller
{
	public function index(){
		echo '<h3>Hello FrPHP Framework !</h3>';
	}
}
