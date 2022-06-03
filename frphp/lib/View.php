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


namespace frphp\lib;

use frphp\extend\Page;

/**
 * 视图基类
 */
class View
{
    protected $_controller;
    protected $_action;
    protected $_cachefile;

    function __construct($controller, $action)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
    }
 
    // 分配变量
    public function assign($name, $value)
    {
        $GLOBALS[$name] = $value;
    }
 
    // 渲染显示
    public function render($name)
    {
        
		if($name!=null){
			//$name = strtolower($name);
			
			if(strpos($name,'@')!==false){
				$controllerLayout =  str_replace('@','',$name);
			}else{
				$controllerLayout =  HOME_VIEW.'/'.Tpl_template.'/' . $name . File_TXT;
			}
			
		}else{
			$controllerLayout =  HOME_VIEW.'/'.Tpl_template.'/' . strtolower($this->_controller) . '/' . $this->_action . File_TXT;

		}
		//去除可能没有的Tpl_template
        $controllerLayout = str_ireplace(['//','\\'],'/',$controllerLayout);
        //判断视图文件是否存在
        if (file_exists($controllerLayout)) {
			
			$this->template($controllerLayout);
			
        } else {
           Error_msg('无法找到视图文件，页面模板：'.$name.File_TXT);
        }
		
		
		
    }
	
	//模板解析
	public function template($controllerLayout){
        extract($GLOBALS);//分配变量到模板中
		$cache_file = Cache_Path.'/'.md5($controllerLayout).'.php';
		$this->_cachefile = $cache_file;//传入系统中
		
		if(APP_DEBUG===true){
			$fp_tp=@fopen($controllerLayout,"r");
			$fp_txt=@fread($fp_tp,filesize($controllerLayout));
			@fclose($fp_tp);
			$fp_txt=$this->template_html($fp_txt);
			$fp_txt = "<?php if (!defined('CORE_PATH')) exit();?>".$fp_txt;
			$fpt_tpl=@fopen($cache_file,"w");
			@fwrite($fpt_tpl,$fp_txt);
			@fclose($fpt_tpl);
		}else if(is_readable($cache_file)!==true){
			$fp_tp=@fopen($controllerLayout,"r");
			$fp_txt=@fread($fp_tp,filesize($controllerLayout));
			@fclose($fp_tp);
			$fp_txt=$this->template_html($fp_txt);
			$fp_txt = "<?php if (!defined('CORE_PATH')) exit();?>".$fp_txt;
			$fpt_tpl=@fopen($cache_file,"w");
			@fwrite($fpt_tpl,$fp_txt);
			@fclose($fpt_tpl);
		}
		
		if(is_readable($cache_file)!==true){
			
			Error_msg('无法找到模板缓存，请刷新后重试，或者检查cache缓存文件夹权限');

		}
		include $cache_file;
		
		
	}
	
	
	//模板分解替换
	public function template_html($content){
		//include标签
		preg_match_all('/\{include=\"(.*?)\"\}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,$this->template_html_include(strtolower($i[1][$k])),$content);
		}
		//loop标签
		preg_match_all('/\{loop (.*?)\}/si',$content,$i);
		$this->check_template_err(substr_count($content, '{/loop}'),count($i[0]),'loop');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,$this->template_html_loop(strtolower($i[1][$k])),$content);
		}
		$content=str_ireplace('{/loop}','<?php } ?>',$content);
		//foreach循环
		preg_match_all('/\{foreach(.*?)\}/si',$content,$i);
		$this->check_template_err(substr_count($content, '{/foreach}'),count($i[0]),'foreach');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,$this->template_html_foreach(strtolower($i[1][$k])),$content);
		}
		$content=str_ireplace('{/foreach}','<?php } ?>',$content);
		//screen标签
		preg_match_all('/\{screen (.*?)\}/si',$content,$i);
		$this->check_template_err(substr_count($content, '{/screen}'),count($i[0]),'screen');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,$this->template_html_screen(strtolower($i[1][$k])),$content);
		}
		$content=str_ireplace('{/screen}','<?php } ?>',$content);
		//for循环
		preg_match_all('/\{for(.*?)\}/si',$content,$i);
		$this->check_template_err(substr_count($content, '{/for}'),count($i[0]),'for');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php for('.$i[1][$k].'){ ?>',$content);
		}
		$content=str_ireplace('{/for}','<?php } ?>',$content);
		//if判断
		preg_match_all('/\{if(.*?)\}/si',$content,$i);
		$this->check_template_err(substr_count($content, '{/if}'),count($i[0]),'if');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php if'.$i[1][$k].'{ ?>',$content);
		}	
		$content=str_ireplace('{else}','<?php }else{ ?>',$content);
		//else if判断
		preg_match_all('/\{else if(.*?)\}/si',$content,$i);
		foreach($i[0] as $k=>$v){
		$content=str_ireplace($v,'<?php }else if'.$i[1][$k].'{ ?>',$content);
		}	
		$content=str_ireplace('{/if}','<?php } ?>',$content);
		//PHP函数解析
		preg_match_all('/\{fun (.*?)\}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php echo '.$i[1][$k].' ?>',$content);
		}
		//PHP常量解析
		preg_match_all('/\{__(.*?)__}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php echo '.$i[1][$k].' ?>',$content);
		}
		//PHP标签解析
		preg_match_all('/\{php(.*?)\/}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php  '.$i[1][$k].' ?>',$content);
		}
		//PHP变量解析
		preg_match_all('/\{\$(.*?)\}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php echo $'.$i[1][$k].' ?>',$content);
		}
		//标签原样输出
		preg_match_all('/\{!--(.*?)\--}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'{'.$i[1][$k].'}',$content);
		}
		return $content;
	}
	//引入公共模板
	public function template_html_include($filename){
		if(strpos($filename,'.')!==false){
			$prefix = '';
		}else{
			$prefix = File_TXT;
		}
		$includefile = str_replace('//','/',HOME_VIEW.'/'.Tpl_template.'/'. Tpl_common .'/'.$filename. $prefix);
		if(!is_file($includefile)){
			Error_msg($includefile.'不存在！');
		}
		$content = file_get_contents($includefile);
		$content = $this->template_html($content);
		return $content;
	}
	//检查模板标签是否错误！
	public function check_template_err($a,$b,$msg){
		if($a!=$b){
			Error_msg($this->_cachefile.'模板中存在不完整'.$msg.'标签，请检查是否遗漏{'.$msg.'}开始或结束符');
		}
	}
	
	//foreach全局标签
	/**
	$content=str_ireplace($v,'<?php foreach('.$i[1][$k].'){  ?>',$content);
	*/
	private function template_html_foreach($f){
		$ff = explode(' as ',$f);
		if(strpos($ff[1],'=>')!==false){
			$fff = explode('=>',$ff[1]);
			$fff[1] = trim($fff[1]);
			return '<?php '.$fff[1].'_n=0;foreach('.$f.'){ '.$fff[1].'_n++; ?>';
		}else{
			$ff[1] = trim($ff[1]);
			return '<?php '.$ff[1].'_n=0;foreach('.$f.'){ '.$ff[1].'_n++;?>';
		}
	}
	
	//loop全局标签
	private function template_html_loop($f){
		preg_match_all('/.*?(\s*.*?=.*?[\"|\'].*?[\"|\']\s).*?/si',' '.$f.' ',$aa);
		$a=array();foreach($aa[1] as $v){$t=explode('=',trim(str_replace(array('"'),"'",$v)));$a=array_merge($a,array(trim($t[0]) => trim($t[1])));}
		if(isset($a['table'])){
			if(strpos($a['table'],'$')!==FALSE){$a['table']=trim($a['table'],"'");}
			$db=$a['table'];
		}else{
			if(!isset($a['tid'])){ exit('缺少table参数！');}
			if(strpos($a['tid'],'$')!==false){
				$db = ' $classtypedata['.trim($a['tid'],"'").']["molds"] ';
			}else{
				if(strpos($a['tid'],',')!==false){
					$tids = explode(',',$a['tid']);
					$db = ' $classtypedata['.trim($tids[0],"'").']["molds"] ';
				}else{
					$db = ' $classtypedata['.trim($a['tid'],"'").']["molds"] ';
				}
			}
			
		}
		if(isset($a['limit'])){
			if(strpos($a['limit'],'$')!==false){
				$limit=trim($a['limit'],"'");
			}else{
				$limit=$a['limit'];
			}
		}else{$limit='null';}
		if(isset($a['notempty'])){$notempty=trim($a['notempty'],"'");}else{$notempty=false;}
		if(isset($a['empty'])){$empty=trim($a['empty'],"'");}else{$empty=false;}
		if(isset($a['fields'])){
			if(strpos($a['fields'],'$')!==false){
				$fields=trim($a['fields'],"'");
			}else{
				$fields=$a['fields'];
			}
			
		}else{$fields='null';}
		if(isset($a['isall'])){$isall=trim($a['isall'],"'");}else{$isall=false;}
		if(isset($a['as'])){$as=$a['as'];}else{$as='v';}
		if(isset($a['day'])){$day=$a['day'];}else{$day=false;}
		if(isset($a['jzpage'])){$jzpage=trim($a['jzpage'],"'");}else{$jzpage='page';}
		if(isset($a['sql'])){$sql=trim($a['sql'],"'");}else{$sql='';}
		if(isset($a['jzcache'])){$jzcache=trim($a['jzcache'],"'");}else{$jzcache=false;}
		if(isset($a['jzcachetime'])){$jzcachetime=trim($a['jzcachetime'],"'");}else{$jzcachetime=30*60;}
		if(isset($a['orderby'])){
			$order=$a['orderby'];
			if(strpos($a['orderby'],'$')!==FALSE){$order=trim($a['orderby'],"'");}
			//$order=' '.str_replace('|',' ',$order).' ';
		}else{$order="' id desc '";}
		if(isset($a['like'])){
			// like='title|学习,keywords|学习' => title like '%学习%' and keywords like '%学习%';
			$lk = array();
			if(strpos($a['like'],',')!==false){
				$like = explode(',',trim($a['like'],"'"));
				foreach($like as $v){
					$s = explode('|',$v);
					if(strpos($s[1],'$')!==false){
						$lk[] = " ".$s[0]." like \'%'.".trim($s[1]).".'%\' ";
					}else{
						$lk[]= " ".$s[0]." like \'%".trim($s[1])."%\' ";
					}
					
				}
				$lk = " and ( ". implode(" or ",$lk)." )";
			}else{
				if(strpos($a['like'],'$')!==false){
					$like = explode('|',trim($a['like'],"'"));
					$lk = " and ".$like[0]." like \'%'.".trim($like[1]).".'%\' ";
				}else{
					$like = explode('|',trim($a['like'],"'"));
					$lk = " and ".$like[0]." like \'%".trim($like[1])."%\' ";
				}
				
			}
			
		}else{ $lk='';}
		if(isset($a['notlike'])){
            $not = array();
            if(strpos($a['notlike'],',')!==false){
                $like = explode(',',trim($a['notlike'],"'"));
                foreach($like as $v){
                    $s = explode('|',$v);
                    if(strpos($s[1],'$')!==false){
                        $not[] = " ".$s[0]." not like \'%'.".trim($s[1]).".'%\' or ".$s[0]." is null  ";
                    }else{
                        $not[]= " ".$s[0]." not like \'%".trim($s[1])."%\' or ".$s[0]." is null ";
                    }
                    
                }
                $notlike = " and ( ". implode(" or ",$not)." )";
            }else{
                if(strpos($a['notlike'],'$')!==false){
                    $like = explode('|',trim($a['notlike'],"'"));
                    $notlike = " and (".$like[0]." not like \'%'.".trim($like[1]).".'%\' or ".$like[0]." is null) ";
                }else{
                    $like = explode('|',trim($a['notlike'],"'"));
                    $notlike = " and (".$like[0]." not like \'%".trim($like[1])."%\' or ".$like[0]." is null)  ";
                }
                
            }
        }else{
		    $notlike = '';
        }
		//不在某个参数范围内
		$notin_sql = '';
		if(isset($a['notin'])){
			if(strpos($a['notin'],'|')!==false){
				$notin = explode('|',trim($a['notin'],"'"));
				if(strpos($notin[1],'$')!==false){
					$notin_sql = ' and '.$notin[0].' not in(\'.'.$notin[1].'.\') ';
				}else{
					$notin_sql = ' and '.$notin[0].' not in('.$notin[1].') ';
				}
				
			}
		}
		//在某个参数范围内
		$in_sql = '';
		if(isset($a['in'])){
			if(strpos($a['in'],'|')!==false){
				$in = explode('|',trim($a['in'],"'"));
				if(strpos($in[1],'$')!==false){
					$in_sql = ' and '.$in[0].' in(\'.'.$in[1].'.\') ';
				}else{
					$in_sql = ' and '.$in[0].' in('.$in[1].') ';
				}
				
			}
		}
		if($sql){
			$sql = " and ('.".$sql.".' ) ";
		}
		unset($a['table']);unset($a['orderby']);unset($a['limit']);unset($a['as']);unset($a['like']);unset($a['notlike']);unset($a['fields']);unset($a['isall']);unset($a['notin']);unset($a['notempty']);unset($a['empty']);unset($a['day']);unset($a['in']);unset($a['sql']);unset($a['jzpage']);unset($a['jzcache']);unset($a['jzcachetime']);
		$pages='';
		$w = ' 1=1 ';
		$ispage=false;
		if($jzpage!='page'){
			if(stripos($jzpage,'$')!==false){
				$jzpage = "'.$jzpage.'";
			}
			$pagenum = "\$pagenum = (int)\$_REQUEST['".$jzpage."'] ? (int)\$_REQUEST['".$jzpage."']  : 1; ";
		}else{
			$pagenum = "\$pagenum = isset(\$frpage) ? \$frpage : (int)\$_REQUEST['page'];";
		}
		
		foreach($a as $k=>$v){
			if(strpos($v,'$')===FALSE){
				//$v = str_ireplace("'",'',$v);
				$v = trim($v,"'");
			}
			
			if($k=='ispage'){
				$ispage=true;
			}else if($k=='tid'){
				
				if(strpos($a['tid'],',')!==false){
					
					if($isall){
						$a['tid'] = trim($a['tid'],"'");
						$tids=explode(',',$a['tid']);
						$ss = [];
						foreach($tids as $s){
							$ss[] = '  tid in(\'.implode(",",$classtypedata['.$s.']["children"]["ids"]).\') ';
						}
						$w.=' and ('.implode(' or ',$ss).' ) ';
					}else{
						$w.=' and tid in('.trim($a['tid'],"'").') ';
					}
					
					
				}else{
					
					if(strpos($a['tid'],'$')!==false){
						if($isall){
							
							$w.= ' and  tid in(\'.implode(",",$classtypedata['.trim($v,"'").']["children"]["ids"]).\') ';
						}else{
							$w.="and tid='.".trim($v,"'").".' ";
						}
						
						
					}else{
						
						if($isall){
							$w.= ' and  tid in(\'.implode(",",$classtypedata['.trim($v,"'").']["children"]["ids"]).\') ';
						}else{
							$w.="and tid=".$v." ";
						}
						
						
					}
				}
				
			}else if($k=='istop'){
				$v = (int)$v;
				$w.=" and jzattr like \'%,1,%\' ";
			}else if($k=='ishot'){
				$v = (int)$v;
				$w.=" and jzattr like \'%,2,%\' ";
			}else if($k=='istuijian'){
				$v = (int)$v;
				$w.=" and jzattr like \'%,3,%\' ";
			}else if($k=='jzattr'){
				if(strpos($v,',')!==false){
					$s = explode(',',$v);
					$s_sql = [];
					foreach($s as $ss){
						$s_sql[]=" jzattr like \'%,".$ss.",%\'  ";
					}
					$w.=" and ( ".implode('or',$s_sql)." ) ";
				}else{
					$w.=" and jzattr like \'%,".$v.",%\'";
				}
				
			}else{
				if(strpos($v,'$')!==FALSE){
					$w.="and ".$k."=\''.".trim($v,"'").".'\' ";
				}else{
					$w.="and ".$k."=\'".$v."\' ";
				}
				
			}
			
			
			
		}
		
		if($notempty){
			//多个字段
			if(strpos($notempty,'|')!==false){
				$notempty = explode('|',$notempty);
				foreach($notempty as $v){
					$w.=' (and trim('.$v.') !="" && trim('.$v.') is not null) ';
				}
				
			}else{
				$w.=' and (trim('.$notempty.') !="" && trim('.$notempty.') is not null)  ';
			}
			
		}
		if($empty){
			//多个字段
			if(strpos($empty,'|')!==false){
				$empty = explode('|',$empty);
				foreach($empty as $v){
					$w.=' and (trim('.$v.') ="" or  trim('.$v.') is null) ';
				}
				
			}else{
				$w.=' and (trim('.$empty.') ="" or trim('.$empty.') is null) ';
			}
			
		}
		if($day){
			$day =str_replace("'",'',$day);
			if(strpos($day,'$')!==false){
				$day = trim($day,"'");
				$w.=" and DATE_SUB(CURDATE(), INTERVAL '".".$day."."' DAY) <= date(FROM_UNIXTIME(addtime))";
			}else{
				$w.=" and DATE_SUB(CURDATE(), INTERVAL ".$day." DAY) <= date(FROM_UNIXTIME(addtime))";
			}
		}
		
		$w .= $notin_sql;
		$w .= $in_sql;
		$w .= $sql;
		$w.= $lk;
		$w.= $notlike;
		$as = trim($as,"'");
		$txt="<?php
		\$".$as."_table =$db;
		\$".$as."_w='".$w."';
		\$".$as."_order=$order;
		\$".$as."_fields=$fields;
		\$".$as."_limit=$limit;";
		
		if($ispage){
			
			$txt .="
			".$pagenum."
			\$".$as."_page = new frphp\Extend\Page(\$".$as."_table);
			\$".$as."_page->sep = '?".$jzpage."=';
			\$".$as."_page->paged = '".$jzpage."';
			\$".$as."_data = \$".$as."_page->where(\$".$as."_w)->fields(\$".$as."_fields)->orderby(\$".$as."_order)->limit(\$".$as."_limit)->page(\$pagenum)->go();
			\$".$as."_pages = \$".$as."_page->pageList();
			\$".$as."_sum = \$".$as."_page->sum;
			\$".$as."_listpage = \$".$as."_page->listpage;
			\$".$as."_prevpage = \$".$as."_page->prevpage;
			\$".$as."_nextpage = \$".$as."_page->nextpage;
			\$".$as."_allpage = \$".$as."_page->allpage;";
		}else{
			
			if($jzcache){
				$txt .= "
				\$cachestr = md5(\$".$as."_table.\$".$as."_w.\$".$as."_order.\$".$as."_fields.\$".$as."_limit);
				$".$as."_data = getCache(\$cachestr);
				if(!$".$as."_data){
					$".$as."_data = M(\$".$as."_table)->findAll(\$".$as."_w,\$".$as."_order,\$".$as."_fields,\$".$as."_limit);
					setCache(\$cachestr,$".$as."_data,$jzcachetime);
				}";
			}else{
				$txt .= "
				$".$as."_data = M(\$".$as."_table)->findAll(\$".$as."_w,\$".$as."_order,\$".$as."_fields,\$".$as."_limit);";
			}
			
		}
		$txt.=' ?>';
		
		return $txt;
		
	}
	
}