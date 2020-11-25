<?php

// +----------------------------------------------------------------------
// | FrPHP { a friendly PHP Framework } 
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://frphp.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2018/02
// +----------------------------------------------------------------------


namespace FrPHP;

// 框架根目录
defined('CORE_PATH') or define('CORE_PATH', __DIR__);

// 内核版本信息
const FrPHP_VERSION     =   '3';

/**
 * FrPHP框架核心
 */
class FrPHP
{
    // 配置内容
    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
		//引入系统配置
		//定义全局常量
		$MyConfig = require(CORE_PATH.'/common/Config.php');
		defined('APP_DEBUG') or define('APP_DEBUG', isset($config['APP_DEBUG']) ? $config['APP_DEBUG'] : $MyConfig['APP_DEBUG']);
		defined('Tpl_style') or define('Tpl_style', isset($config['Tpl_style']) ? $config['Tpl_style'] : $MyConfig['Tpl_style']);
		defined('Tpl_common') or define('Tpl_common', isset($config['Tpl_common']) ? $config['Tpl_common'] : $MyConfig['Tpl_common']);
		defined('APP_HOME') or define('APP_HOME', isset($config['APP_HOME']) ? $config['APP_HOME'] : $MyConfig['APP_HOME']);
		defined('HOME_MODEL') or define('HOME_MODEL', isset($config['HOME_MODEL']) ? $config['HOME_MODEL'] : $MyConfig['HOME_MODEL']);
		defined('HOME_CONTROLLER') or define('HOME_CONTROLLER', isset($config['HOME_CONTROLLER']) ? $config['HOME_CONTROLLER'] : $MyConfig['HOME_CONTROLLER']);
		defined('HOME_VIEW') or define('HOME_VIEW', isset($config['HOME_VIEW']) ? $config['HOME_VIEW'] : $MyConfig['HOME_VIEW']);
		defined('File_TXT') or define('File_TXT', isset($config['File_TXT']) ? $config['File_TXT'] : $MyConfig['File_TXT']);
		defined('SessionTime') or define('SessionTime', isset($config['SessionTime']) ? $config['SessionTime'] : $MyConfig['SessionTime']);
		defined('StopLog') or define('StopLog', isset($config['StopLog']) ? $config['StopLog'] : $MyConfig['StopLog']);
		defined('DefaultController') or define('DefaultController', isset($config['DefaultController']) ? $config['DefaultController'] : $MyConfig['DefaultController']);
		defined('DefaultAction') or define('DefaultAction', isset($config['DefaultAction']) ? $config['DefaultAction'] : $MyConfig['DefaultAction']);
		defined('open_url_route') or define('open_url_route', isset($config['open_url_route']) ? $config['open_url_route'] : $MyConfig['open_url_route']);
		defined('open_redis_session') or define('open_redis_session', isset($config['open_redis_session']) ? $config['open_redis_session'] : $MyConfig['open_redis_session']);
		defined('Cache_Path') or define('Cache_Path', isset($config['Cache_Path']) ? $config['Cache_Path'] : $MyConfig['Cache_Path']);
		defined('Session_Path') or define('Session_Path', isset($config['Session_Path']) ? $config['Session_Path'] : $MyConfig['Session_Path']);
		defined('APP_LANG') or define('APP_LANG', isset($config['APP_LANG']) ? $config['APP_LANG'] : $MyConfig['APP_LANG']);
		defined('APP_LANG_REQUREST') or define('APP_LANG_REQUREST', isset($config['APP_LANG_REQUREST']) ? $config['APP_LANG_REQUREST'] : $MyConfig['APP_LANG_REQUREST']);
		defined('OPEN_MOBILE') or define('OPEN_MOBILE', isset($config['OPEN_MOBILE']) ? $config['OPEN_MOBILE'] : $MyConfig['OPEN_MOBILE']);
		defined('PC_VIEW') or define('PC_VIEW', isset($config['PC_VIEW']) ? $config['PC_VIEW'] : $MyConfig['PC_VIEW']);
		defined('MOBILE_VIEW') or define('MOBILE_VIEW', isset($config['MOBILE_VIEW']) ? $config['MOBILE_VIEW'] : $MyConfig['MOBILE_VIEW']);
		//引入系统函数
		require(CORE_PATH.'/common/Functions.php');
		//引入项目函数
		if(file_exists(APP_PATH.CONF_PATH.'/Functions.php')){
			$ext_fun = APP_PATH.CONF_PATH.'/Functions.php';
			if(file_exists($ext_fun)){
				require($ext_fun);
			}
		}
		//检查是否开启手机端模板
		if(OPEN_MOBILE){
			if(isMobile() && MOBILE_VIEW!=''){
				//wap
				$tpl = MOBILE_VIEW;
			}else{
				$tpl = PC_VIEW;
			}
		}else{
			$tpl = PC_VIEW;
		}
		define('Tpl_template', $tpl);
		
		//引入扩展函数
		$Extend = scandir(CORE_PATH.'/Extend');
		//var_dump($Extend);
		foreach($Extend as $v){
			if(strpos($v,'.php')!==false){
				include  CORE_PATH.'/Extend/'.$v;
			}
		}
		
		//检查缓存文件是否存在
		if(!is_dir(Cache_Path)){
			mkdir(Cache_Path,0777,true);
		}
		if(!is_dir(Cache_Path.'/tmp')){
			mkdir(Cache_Path.'/tmp',0777,true);
		}
		
		//设置时区
		@date_default_timezone_set('PRC');
    }

    // 运行程序
    public function run()
    {
        spl_autoload_register(array($this, 'loadClass'));
		$this->setDbConfig();
        $this->setReporting();
        $this->removeMagicQuotes();
        //$this->unregisterGlobals();
        $this->route();
		
    }

    // 路由处理
    public function route()
    {
		
		
		//检查是否开启redis_session ---2019/09/05 留恋风
		if(open_redis_session){
			$session = new \SessionRedis($this->config['redis']);
			session_set_save_handler($session,true);
			if (!isset($_COOKIE['PHPSESSID'])) {
				session_set_cookie_params($this->config['redis']['EXPIRE']);
				if(!session_id()){ session_start();}
			} else {
				if(!session_id()){ session_start();}
				setcookie('PHPSESSID', $_COOKIE['PHPSESSID'], time() + $this->config['redis']['EXPIRE'],'/');
			}
		}else{
			
			//开启SESSION,并设置600s缓存时间
			//start_session(SessionTime);
			
			$session = new \FrSession(array('save_path'=>Session_Path,'life_time'=>SessionTime));
			session_set_save_handler($session,true);
			if (!isset($_COOKIE['PHPSESSID'])) {
				session_set_cookie_params(SessionTime);
				if(!session_id()){ session_start();}
			} else {
				if(!session_id()){ session_start();}
				setcookie('PHPSESSID', $_COOKIE['PHPSESSID'], time() + SessionTime,'/');
			}
			
			
		}
		
      	
		//引入自定义路由
		
		$route_ok = false;
		
		if(isset($_SERVER['argv']) && !isset($_SERVER['REQUEST_URI'])){
			$url = urldecode($_SERVER['argv'][1]);
		}else{
			$url = urldecode($_SERVER['REQUEST_URI']);
		}
		
		$method = '';
		if(open_url_route && file_exists(APP_PATH.CONF_PATH.'/route.php')){
			$open_url_route = include (APP_PATH.CONF_PATH.'/route.php');
			$urls = '';
			foreach($open_url_route as $k=>$v){
				if($v!='' && $v[0]!='' && $v[1]!=''){
					$route_ok = preg_match_all($v[0],$url,$matches);
					$urls = $v[1];
					$method = strtoupper($v[2]);
					
					if($route_ok){
						
						break;
					}
				}
			}
			if($route_ok){
				//print_r($matches);
				foreach($matches as $k=>$v){
					$urls = str_replace('$'.$k,$v[0],$urls);
				}
				$url = $urls;

			}
			
			
			
		}else{
			$open_url_route = [];
			
		}
		$url = format_param($url,1);
		define('REQUEST_URI',$url);
        $controllerName = DefaultController;
        $actionName = DefaultAction;
        $param = array();

       // $url = urldecode($url);
        // 清除?之后的内容
        $position = strpos($url, '?');
        $url = $position === false ? $url : substr($url, 0, $position);
		//删除入口文件字符串
		if(strpos($url,'.php')!==False){
			//获取入口文件
            $ds = strpos($url,'.php');
            define('APP_URL',substr($url,0,($ds+4)));
            $url = substr(strstr($url,'.php'),4);
		}else{
            define('APP_URL','/index.php');
        }
		//去除最后的.html后缀
		if(File_TXT!=''){
			if(strpos($url,File_TXT)!==False){
				$url = str_ireplace(File_TXT,'',$url);
			}	
		}
		
        // 删除前后的“/”
        $url = trim($url, '/');
        if ($url){
            // 使用“/”分割字符串，并保存在数组中
            $urlArray = explode('/', $url);
            // 删除空的数组元素
            //$urlArray = array_filter($urlArray);
			foreach($urlArray as $k=>$v){
				if($v!=''){
					$urlArray[$k] = $v;
				}
			}
			// 获取控制器名
			$controllerName = ucfirst($urlArray[0]);
			// 获取动作名
			array_shift($urlArray);
	  
            $actionName = $urlArray ? $urlArray[0] : $actionName;
            
            // 获取URL参数
            array_shift($urlArray);
            $param = $urlArray ? $urlArray : array();
			
        }else{
        	//表示第一次安装或者进入默认控制器，默认方法
        	if(!file_exists(APP_PATH.APP_HOME.'/'.HOME_CONTROLLER.'/'.$controllerName.'Controller.php')){
        		$res = mkdir(APP_PATH.APP_HOME,0777,true);
        		if(!$res){
					exit('根目录没有创建文件夹权限，请手动创建'.APP_PATH.APP_HOME.'项目文件夹目录！');
				}
				$res = mkdir(APP_PATH.APP_HOME.'/'.HOME_CONTROLLER,0777,true);
				if($res){
					file_put_contents(APP_PATH.APP_HOME.'/'.HOME_CONTROLLER.'/IndexController.php',file_get_contents(CORE_PATH.'/default/IndexController.php'));
				}
        	}
        	
        }

        // 判断控制器和操作是否存在
		$controller = APP_HOME.'\\'.HOME_CONTROLLER.'\\'. $controllerName . 'Controller';
		if (!class_exists($controller)) {
			Error_msg($controller.'控制器不存在！');
        }
        if(!method_exists($controller, $actionName)){
        	Error_msg($actionName.'方法不存在！');
        }
        //定义全局控制器及方法常量
		define('APP_CONTROLLER',$controllerName);
        define('APP_ACTION',$actionName);
        
		if(open_url_route && $route_ok){
			switch($method){
				case 'GET':
				$param = (count($this->stringGet($param))>0) ? array_merge($this->stringGet($param),$_GET) : $_GET;
				break;
				case 'POST':
				$param = $_POST;
				break;
				default:
					$_GET = (count($this->stringGet($param))>0) ? array_merge($this->stringGet($param),$_GET) : $_GET;
					$param = (count($_GET)>0) ? array_merge($_GET,$_REQUEST) : $_REQUEST;
				break;
			}
			
			
		}else{
			$_GET = (count($this->stringGet($param))>0) ? array_merge($this->stringGet($param),$_GET) : $_GET;
			$param = (count($_GET)>0) ? array_merge($_GET,$_REQUEST) : $_REQUEST;
		}

		//$dispatch = new $controller($controllerName, $actionName ,$param);
		$dispatch = new $controller($param);
        call_user_func_array(array($dispatch, $actionName), $param);

		
		
		
    }
	
	//将链接参数转为GET传值
	public function stringGet($urlarray){
		$data = array();
		foreach($urlarray as $k=>$v){
			if(($k+1)%2==1){
				if(!isset($urlarray[$k+1])){$urlarray[$k+1]=null;}
				$data[$v] = $urlarray[$k+1];
			}
		}
		return $data;
	
	
	}
	

    // 检测开发环境
    public function setReporting()
    {
     
        if(APP_DEBUG === true) {
			error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            ini_set('display_errors','On');
        } else {
            error_reporting(0);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
        }
    }

    // 删除敏感字符
    public function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    // 检测敏感字符并删除
    public function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? $this->stripSlashesDeep($_GET ) : '';
            $_POST = isset($_POST) ? $this->stripSlashesDeep($_POST ) : '';
            $_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        }
    }

    // 检测自定义全局变量并移除。因为 register_globals 已经弃用，如果
    // 已经弃用的 register_globals 指令被设置为 on，那么局部变量也将
    // 在脚本的全局作用域中可用。 例如， $_POST['foo'] 也将以 $foo 的
    // 形式存在，这样写是不好的实现，会影响代码中的其他变量。 相关信息，
    // 参考: http://php.net/manual/zh/faq.using.php#faq.register-globals
    public function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    // 配置数据库信息
    public function setDbConfig()
    {
        if ($this->config['db']) {
            define('DB_PATH', $this->config['db']['dbpath']);
            define('DB_TYPE', $this->config['db']['type']);
            define('DB_HOST', $this->config['db']['host']);
            define('DB_NAME', $this->config['db']['dbname']);
            define('DB_PREFIX', $this->config['db']['prefix']);
            define('DB_USER', $this->config['db']['username']);
            define('DB_PASS', $this->config['db']['password']);
            define('DB_PORT', $this->config['db']['port']);
        }
		
    }

    // 自动加载类
    public function loadClass($className)
    {
        $classMap = $this->classMap();

        if (isset($classMap[$className])) {
            // 包含内核文件
            $file = $classMap[$className];
        } elseif (strpos($className, '\\') !== false) {
            // 包含应用（application目录）文件
            $file = APP_PATH . str_replace('\\', '/', $className) . '.php';
            if (!is_file($file)) {
                return;
            }
        } else {
            return;
        }

        include $file;

        // 这里可以加入判断，如果名为$className的类、接口或者性状不存在，则在调试模式下抛出错误
    }

    // 内核文件命名空间映射关系
    protected function classMap()
    {
        return [
            'FrPHP\lib\Controller' => CORE_PATH . '/lib/Controller.php',
            'FrPHP\lib\Model' => CORE_PATH . '/lib/Model.php',
            'FrPHP\lib\View' => CORE_PATH . '/lib/View.php',
            'FrPHP\db\DBholder' => CORE_PATH . '/db/DBholder.php',
            'FrPHP\db\Sqlite' => CORE_PATH . '/db/Sqlite.php',
            
        ];
    }
}

//检查是否存在配置文件
if(!defined('CONF_PATH')){
	define('CONF_PATH','conf');
}
if(!file_exists(APP_PATH.CONF_PATH.'/config.php')){
	if(!file_exists(APP_PATH.CONF_PATH)){
		$res = mkdir(APP_PATH.CONF_PATH,0777,true);
		if(!$res){
			exit('根目录没有创建文件夹权限，请手动创建'.CONF_PATH.'配置文件夹目录！');
		}
	}
	if(!file_exists(HOME_VIEW)){
		$res = mkdir(HOME_VIEW,0777,true);
		if(!$res){
			exit('根目录没有创建文件夹权限，请手动创建'.HOME_VIEW.'模板文件夹目录！');
		}
		$res = mkdir(HOME_VIEW.'/pc',0777,true);
		if(!$res){
			exit('根目录没有创建文件夹权限，请手动创建'.HOME_VIEW.'/pc模板文件夹目录！');
		}
	}
	$conf = array ('db' => 
	  array (
	    'host' => '127.0.0.1',
	    'dbname' => '',
	    'username' => '',
	    'password' => '',
	    'prefix' => 'jz_',
	    'port' => '3306',
	    'type' => 'mysql',
	    'dbpath' => '',
	  ),'redis' => 
	  array (
	    'SAVE_HANDLE' => 'Redis',
	    'HOST' => '127.0.0.1',
	    'PORT' => 6379,
	    'AUTH' => NULL,
	    'TIMEOUT' => 0,
	    'RESERVED' => NULL,
	    'RETRY_INTERVAL' => 100,
	    'RECONNECT' => false,
	    'EXPIRE' => 1800,
	  ),
	  'APP_DEBUG' => true,
	  'PC_VIEW' => 'pc',//PC模板
	  'MOBILE_VIEW' => 'mobile',//手机模板
	);
	$res = file_put_contents(APP_PATH .CONF_PATH.'/config.php', '<?php return ' . var_export($conf, true) . '; ?>');
	if(!$res){
		exit(CONF_PATH.'目录没有创建文件夹权限，请手动创建config.php！');
	}
	

}

// 加载配置文件
$config = require(APP_PATH .CONF_PATH.'/config.php');

//实例化核心类
(new FrPHP($config))->run();