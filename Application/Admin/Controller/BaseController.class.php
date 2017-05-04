<?php
	namespace Admin\Controller;
	use Think\Controller;
	use Think\Auth;

	class BaseController extends Controller {
		public function _initialize() {
			//1、判断是否已经登录
			if(!$this->_is_login()) $this->redirect('Public/login');

			//2、判断用户的访问路径
			$path = CONTROLLER_NAME . '/' . ACTION_NAME;
			$auth_control = $this->change_path($path);

			//3、判断用户是否有权限访问
			if($auth_control['auth_check']) {
				$auth = new Auth();
            	$auth_rst = $auth->check($auth_control['path'], session('admin_info.id'));

            	if($auth_rst === false) {
	                if(IS_AJAX){
	                   	die(json_encode(array('code'=>-10, 'msg'=>'没有权限!')));
	                }else{
	                    $this->error('没有权限!');
	                    die;
	                }
            	}

            	define(NOW_PATH, $auth_control['path']);
            	define(NOW_OPEN, $auth_control['open']);
			}
		}

		/**
		 * 权限控制
		 * @param  [type] $path [description]
		 * @return [type]       [description]
		 */
		private function change_path($path) {
			//菜单权限
			$m_lst = array(
					//权限管理
					'Auth/menu_lst',        //菜单管理
					'Auth/role_lst',        //角色管理
					'Auth/admin_lst',       //用户管理
					//首页轮播图管理
					'Banner/index',         //轮播图列表
					//分类管理
					'Category/lst',         //分类列表
					//精选相关管理
					'FineArticle/lst',      //精选列表
					//原创相关管理
					'Original/lst',         //原创列表
					//活动相关管理
					'Activity/lst',         //活动列表
					//个人认证相关管理
					'Renzheng/lst',         //个人认证列表
					'User/lst',             //用户列表
					//网站相关配置
					'WebConfig/conf_email', //邮箱配置
					'BackGround/add_bg',    //登录页背景图
					'Advert/lst',           //首页广告
					'WebConfig/conf_search',//搜索配置
					//组织机构管理
					'Mechanism/index',      //组织机构列表
					//评论管理
					'Comment/lst',          //评论列表
				);

			//菜单页面权限
			$son_lst = array(
					//权限管理
					'Auth/menu_add'         =>   'Auth/menu_lst',        //菜单添加修改
					'Auth/role_add'         =>   'Auth/role_lst',        //角色添加修改
					'Auth/admin_add'        =>   'Auth/admin_lst',       //用户添加修改
					//首页轮播图管理
					'Banner/op'             =>   'Banner/index',         //轮播图添加
					'Banner/edit'           =>   'Banner/index',         //轮播图编辑
					//分类管理
					'Category/op'           =>   'Category/lst',         //分类添加
					'Category/edit'         =>   'Category/lst',         //分类编辑
					//精选相关管理
					'FineArticle/op'        =>   'FineArticle/lst',      //精选添加和编辑
					//原创相关管理
					'Original/look'         =>   'Original/lst',         //原创查看
					//活动相关管理
					'Activity/op'           =>   'Activity/lst',         //活动添加和编辑
					//组织机构管理
					'Mechanism/op'          =>   'Mechanism/index',      //添加组织
					'Mechanism/member'      =>   'Mechanism/index',      //查看组织
					//个人认证相关管理
					'User/to_co_ident'      =>   'User/lst',             //认证邀请设计师
					'User/to_cc_ident'      =>   'User/lst',             //认证公司设计师
					//网站相关配置
					'Advert/advert_edit'    =>   'Advert/lst',           //提交修改首页广告
					//评论管理
					'Comment/eidt_com'      =>   'Comment/lst',          //评论编辑
				    'Comment/com_del'       =>   'Comment/lst',          //评论删除
				);

			//1、菜单权限验证
			if(in_array($path, $m_lst)) {
				$open = M('auth_rule')->where(array('name'=>$path))->getField('pid');
				return array('auth_check'=>1, 'path'=>$path, 'open'=>$open);
			}

			//2、页面权限验证
			$now_path = $son_lst[$path];
			if($now_path) {
				$open = M('auth_rule')->where(array('name'=>$now_path))->getField('pid');
				return array('auth_check'=>1, 'path'=>$now_path, 'open'=>$open);
			}

			//3、不用验证权限
			return array('auth_check'=>0);
		}


		/**
		 * 检测是否登录
		 * @return boolean [description]
		 */
		private function _is_login() {
			if(session('admin_info.id')) {
				return true;
			}else{
				return false;
			}
		} 

		/**
		 * 分页操作通用接口
		 * @param
		 *     $Data   传入的model类
		 *     $map    过滤的参数
		 *     $order  排序的方式
		 *     $field  查询结果显示的字段
		 *     $joinArray 需要left的sql语句
		 *         sql : join 的sql语句
		 *         type: join的执行方式，比如left即表示left join
		 *     $pageSize   查询的条件  默认为10条
		 *     $sql    直接执行sql，暂时不支持!!!!
		 */
		function page(&$Data,$map=array(),$order = '',$field='',$joinArray=array(), $group='',$pageSize=10, $sql=''){
			$filter = $this->get_filter();
			$cond=array();
			if(!empty($map)){
				$cond[] = array_merge($filter, $map);
			}
			$exact = $this->get_filter_exact();
			if(!empty($exact)){
				$cond[] = array_merge($filter, $exact);
			}

			if(!empty($joinArray)){
				foreach ($joinArray as $join){
					$joinType = isset($join['type']) ? $join['type'] : 'left';
					$Data->join($join['sql'],$joinType);
				}
			}
			if(!empty($group)){
				$cond[] = array_merge($filter, $group);
			}

			if($group){
				$count = $Data->where($cond[0])->count("distinct({$group})");// 查询满足要求的总记录数
			}else{
				$count = $Data->where($cond[0])->count();// 查询满足要求的总记录数
			}


			// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
			$nowPage = $_GET['p'];
			$pageTotal = ceil($count / $pageSize);
			if ($nowPage > $pageTotal) $nowPage = $pageTotal;
			$nowPage = $_GET['p'] ? $_GET['p']:1;
			if (!empty($sql)){
				$list = $Data->query($sql);
			}else{
				if(!empty($map))
					$Data->where($map);

				if(!empty($field))
					$Data->field($field);

				if(!empty($order))
					$Data->order($order);

				if(!empty($group)){
					$Data->group($group);
				}

				if(!empty($joinArray)){
					foreach ($joinArray as $join){
						$joinType = isset($join['type']) ? $join['type'] : 'left';
						$Data->join($join['sql'],$joinType);
					}
				}
				//生成sql
				if(COM_EXCEL != 1) $Data->limit((intval($nowPage)-1)*$pageSize,$pageSize);
				
				$list = $Data->where($filter)->order($order)->select();

				foreach ($filter as &$f){
					if($f[0] == 'LIKE'){
						$f[1] = str_replace('%', '', $f[1]);
					}
				}
				$this->assign('search',$filter);
			}
			$this->assign('page',$nowPage);            // 赋值所在页
			$this->assign('list',$list ? $list : array());               // 赋值数据集
			$this->assign('pageTotal', $pageTotal);    // 总共多少页
			$this->assign('nowPage', $nowPage);        // 现在在第几页
			$this->assign('total_count', $count);      //总条数
			if(empty($_GET['p'])){
				if(strstr(__SELF__,'?')){
					$this->assign('nextPage', __SELF__.'&p=2');        // 前一页
				}else{
					$this->assign('nextPage', __SELF__.'?p=2');        // 前一页
				}
			}else{
				if($nowPage > 1){
					$this->assign('prevPage', preg_replace('/p=[0-9]*/', 'p='.($nowPage-1), __SELF__));        // 前一页
				}
				if($nowPage < $pageTotal){
					$this->assign('nextPage', preg_replace('/p=[0-9]*/', 'p='.($nowPage+1), __SELF__));        // 后一页
				}
			}

		    if(stristr(__SELF__, 'p=')){
		    	$url = __SELF__;
		    	$temp_arr = explode('p=', $url);
		    	$this->assign('hrefPage',$temp_arr[0].'p=');
		    }else{
		    	if(strstr(__SELF__,'?')) {
		    		$this->assign('hrefPage',__SELF__.'&p=');
		    	}else{
		    		$this->assign('hrefPage',__SELF__.'?p=');
		    	}
		    }
		    
			if($nowPage == $pageTotal) {
				define('OVER_PAGE',1);
			}

			return $list;
		}

		private function get_filter(){
			$search = I('get.search');
			$map = array();
			if(is_array($search)){
				foreach ($search as $k=>$v){
					if(!empty($v)){
						$map[$k] = array('LIKE','%'.$v.'%');
					}
				}
			}
			return $map;
		}

		private function get_filter_exact(){
			$search = I('get.exact');
			$map = array();
			if(is_array($search)){
				foreach ($search as $k=>$v){
					if(!empty($v)){
						$map[$k] = array('EQ',$v);
					}
				}
			}
			return $map;
		}

		function sql_page($sql_count,$sql_data,$pageSize = 10) {
			//1、获取总条数
			$count = M()->query($sql_count);  
			$count = $count[0]['num'];
			
			//2、分页参数
		    $nowPage = I('p') ? I('p'):1;
		    $pageTotal = ceil($count / $pageSize);
			if ($nowPage > $pageTotal) $nowPage = $pageTotal;
			if ($nowPage <= 0) $nowPage = 1;

			//3、获取数据
			//>>判断是否是导出数据
			if(COM_EXCEL!=1){
				$sql_data = $sql_data.' limit '.($nowPage-1)*$pageSize.','.$pageSize;
			}
			$list = M()->query($sql_data);
			$this->assign('page',$nowPage);            // 赋值所在页
		    $this->assign('pageTotal', $pageTotal);    // 总共多少页
		    $this->assign('nowPage', $nowPage);        // 现在在第几页
		    $this->assign('pageSize', $pageSize);      // 每一页显示的条数
		    $this->assign('total_count', $count);      //总条数
		    if(empty($_GET['p'])){
		        if(strstr(__SELF__,'?')){
		            $this->assign('nextPage', __SELF__.'&p=2');        // 前一页
		        }else{
		            $this->assign('nextPage', __SELF__.'?p=2');        // 前一页
		        }
		    }else{
		        if($nowPage > 1){
		            $this->assign('prevPage', preg_replace('/p=[0-9]*/', 'p='.($nowPage-1), __SELF__));        // 前一页
		        }
		        if($nowPage < $pageTotal){
		            $this->assign('nextPage', preg_replace('/p=[0-9]*/', 'p='.($nowPage+1), __SELF__));        // 后一页
		        }
		    }
		    
		    if(stristr(__SELF__, 'p=')){
		    	$url = __SELF__;
		    	$temp_arr = explode('p=', $url);
		    	$this->assign('hrefPage',$temp_arr[0].'p=');
		    }else{
		    	if(strstr(__SELF__,'?')) {
		    		$this->assign('hrefPage',__SELF__.'&p=');
		    	}else{
		    		$this->assign('hrefPage',__SELF__.'?p=');
		    	}
		    }
		    
			if($nowPage == $pageTotal) {
				define('OVER_PAGE',1);
			}

		    return $list ? $list : array();
		}

	}

?>