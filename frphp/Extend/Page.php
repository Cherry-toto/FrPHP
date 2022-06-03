<?php

// +----------------------------------------------------------------------
// | FrPHP { a friendly PHP Framework } 
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://frphp.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2018/04/20
// +----------------------------------------------------------------------


namespace frphp\extend;

	class Page {
		//分页表
		public $table = '';
		//总条数
		public $sum = 0;
		//总页数
		public $allpage = 0;
		//上一页
		public $prevpage = '';
		//下一页
		public $nextpage = '';
		//每页条数
		public $limit = 10;
		//分页从第几开始
		public $limit_t = 0;
		//当前页码
		public $currentPage = 1;
		//分页页码数
		public $pv = 5;
		//系统分页链接
		public $url = '';
		//分页分隔符
		public $sep = '?page=';
		//SQL
		public $sql = null;
		//排序
		public $order = null;
		//字段
		public $fields = null;
		//当前分页数据
		public $datalist = array();
		//当前分页数组
		public $listpage = array();
		//分页url设置
		public $typeurl = '';

		
		public function __construct($table=''){
			
			$this->table = $table;
			
		}
		
		
		public function getUrl(){
			$request_uri = $_SERVER["REQUEST_URI"];    
            if(strpos($request_uri,APP_URL)!==false){
				$app_url = '/'.APP_URL.'/';
			}else{
				$app_url = '/';
			}
			$this->sep = '?page=';
			if(isset($_GET['page'])){
				unset($_GET['page']);
			}
			$url = WWW.$_SERVER['REDIRECT_URL'];
			if(count($_GET)>0){
				$this->sep = '&page=';
				$url .= '?'.http_build_query($_GET);
			}
			
			
			return $url;
            
		}
		
		public function pageList(){
			/**
				首页url  		home
				上一页url		prev
				下一页url       next
				当前页url       current
				总页数  	    allpage
				当前页码		current_num
				普通页码组      list
				最后一页url		last

			**/
			$listpage = array(
				'home' => null,
				'prev' => null,
				'next' => null,
				'current' => null,
				'allpage' => 0,
				'current_num' => 0,
				'list' => null,
				'last' => null,
			);
			
			$url = $this->getUrl();
			if( strpos($url,$this->sep)!==false && $this->currentPage!=1){
				$urls = explode($this->sep,$url);
				$num = array_pop($urls);
				if(is_numeric($num)){
					  $url = implode($this->sep,$urls);
				}

			}
			
			$this->url = $url;
			$list = '';
			$request_uri = $_SERVER["REQUEST_URI"];    
           
			
			$listpage['home'] = $this->url;
			$num = floor($this->pv/2);
            $start = $this->currentPage-$num;
            $start = ($this->currentPage+$num) > $this->allpage ? ($this->allpage-$this->pv+1) : $start;
            $start = $start<1 ? 1 : $start;
            
            $end = $this->currentPage+$num;
            $end = $end>$this->allpage ? $this->allpage : $end;
            $end = $start<$num ? ($this->pv>=$this->allpage ? $this->allpage : $this->pv) : $end;
            while($start<=$end){
                $urlx = $start==1 ? $this->url : $this->url.$this->sep.$start;
                if($start==$this->currentPage){
                    $list.='<li class="active" ><a >'.$this->currentPage.'</a></li>';
                    $listpage['current'] = $urlx;
                    $listpage['current_num'] = $this->currentPage;
                }else{
                    $list .= '<li><a href="'.$urlx.'" data-page="'.$start.'">'.$start.'</a></li>';
                }
                
                $listpage['list'][] = array('url'=>$urlx,'num'=>$start);
                $start++;
            }
			$listpage['allpage'] = $this->allpage;
			$prev_url = $this->currentPage==1 ? '' : $this->url.$this->sep.($this->currentPage-1);
			$prev = '<li><a href="'.$prev_url.'" class="layui-laypage-prev" data-page="'.($this->currentPage-1).'"><em>&lt;</em></a></li>';
			
			if($this->currentPage!=1){
				$this->prevpage = $this->url.$this->sep.($this->currentPage-1);
			}
			$next = '<li><a href="'.$this->url.$this->sep.($this->currentPage+1).'" class="layui-laypage-next" data-page="'.($this->currentPage+1).'"><em>&gt;</em></a></li>';
			
			if($this->currentPage != $this->allpage && $this->allpage>1){
			$this->nextpage = $this->url.$this->sep.($this->currentPage+1);	
			}
			
			$all = '<li><a href="javascript:;" data-page="'.$this->currentPage.'">总共'.$this->currentPage.'/'.$this->allpage.'</a></li>';
			$last_url = $this->allpage==1 ? $this->url : $this->url.$this->sep.$this->allpage;
			$last = '<li><a href="'.$last_url.'" class="layui-laypage-prev" data-page="'.$this->allpage.'"><em>尾页</em></a></li>';
			
			$ext = '<div class="pagination"><ul>';
			$list = $all.$list;
			
			if($this->currentPage!=1){
				$list = $prev.$list;
				$listpage['prev'] = $this->prevpage;
			}
			if($this->currentPage<$this->allpage){
				$list .= $next;
				$listpage['next'] = $this->nextpage;
			}
			if($this->allpage > $this->pv){
				$list .= $last;
			}
			$listpage['last'] = $last_url;
			$list = $ext.$list.'</ul></div>';
			$this->listpage = $listpage;
			return $list;
			
		}
		
		public function where($sql=null){
			$this->sql = $sql;
			return $this;
		}
		public function orderby($orders=null){
			$this->order = $orders;
			return $this;
		}
		public function limit($limit=null){
			if($limit==null){
				$this->limit = $this->limit;
			}else{
				if(strpos($limit,',')!==false){
					$limit_t = explode(',',$limit);
					$this->limit = (int)$limit_t[1];
					$this->limit_t = (int)$limit_t[0];
				}else{
					$this->limit = $limit;
				}

			}

			return $this;
		}
		public function fields($fields=null){
			$this->fields = $fields;
			return $this;
		}
		public function page($p=1){
			$this->currentPage = (int)$p;
			return $this;
		}
		
		
		public function setPage($config){
			if(isset($config['order'])){
				$this->order = $config['order'];
			}
			if(isset($config['fields'])){
				$this->fields = $config['fields'];
			}
			if(isset($config['limit'])){
				$this->limit = $config['limit'];
			}
			if(isset($config['page'])){
				$this->currentPage = $config['page'];
			}
			
			return $this;
		}
		
		public function go(){
			if($this->currentPage!=1){
				$limitsql = (($this->limit*($this->currentPage-1)) - ($this->limit_t)).','.$this->limit;
				//1-0:1  2-2:3
			}else{
				if($this->limit_t!=0){
					$limitsql = $this->limit_t.','.$this->limit;
				}else{
					$limitsql = $this->limit;
				}
				
			}
			
			$this->datalist = M($this->table)->findAll($this->sql,$this->order,$this->fields,$limitsql);
			
			$this->sum = M($this->table)->getCount($this->sql);
			$this->limit = $this->limit;

			$allpage = ceil($this->sum/$this->limit);
			if($allpage==0){$allpage=1;}
			$this->allpage = $allpage;
			return $this->datalist;
		}
		
		//一步到位
		public function goPage($sql=null,$order=null,$fields=null,$limit=10){
			$this->sql = $sql;
			$this->order = $order;
			$this->fields = $fields;
			if(strpos($limit,',')!==false){
				$limit_t = explode(',',$limit);
				$this->limit = (int)$limit_t[1];
				$this->limit_t = (int)$limit_t[0];
			}else{
				$this->limit = $limit;
			}
			
			if($this->currentPage!=1){
				$limitsql = (($this->limit*($this->currentPage-1)) - ($this->limit_t)).','.$this->limit;
				//1-0:1  2-2:3
			}else{
				if($this->limit_t!=0){
					$limitsql = $this->limit_t.','.$this->limit;
				}else{
					$limitsql = $this->limit;
				}
				
			}
			
			$this->datalist = M($this->table)->findAll($this->sql,$this->order,$this->fields,$limitsql);
			$this->sum = M($this->table)->getCount($sql);
			$this->limit = $limit;
			$this->allpage = ceil($this->sum/$this->limit);
			return $this->datalist;
		}
		
		// SQL处理
		public function goSql(){
			if($this->currentPage!=1){
				$limitsql = (($this->limit*($this->currentPage-1)) - ($this->limit_t)).','.$this->limit;
				//1-0:1  2-2:3
			}else{
				if($this->limit_t!=0){
					$limitsql = $this->limit_t.','.$this->limit;
				}else{
					$limitsql = $this->limit;
				}
				
			}
			$sum = M()->findSql($this->sql);
			$this->sum = count($sum);
			$orderby = $this->order ? ' order by '.$this->order : '';
			$limit = ' limit '.$limitsql;
			$sql = $this->sql.' '.$orderby.' '.$limit;
			$this->datalist = M()->findSql($sql);
			$this->limit = $this->limit;
			
			$allpage = ceil($this->sum/$this->limit);
			if($allpage==0){$allpage=1;}
			$this->allpage = $allpage;
			return $this->datalist;
		}
		
		public function goCount($sql){
			$n = M()->findSql($sql);
			$this->sum = count($n);
			return $this;
		}
		
		
		
		
		
		
		
		
	}

















?>