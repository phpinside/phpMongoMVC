<?php

!defined('IN_APP') && exit('Access Denied');

class controlbase {

	public  $ip;
	public  $time;
	public  $db;//mongodb
	public  $cache;
	public  $user = array();
	public  $setting = array();
	protected  $get = array();
	protected  $post = array();
	protected $safepost = array('1'=>'分钟','60'=>'小时','1440'=>'天');

	function __construct( $get,  $post) {
		$this->time = time();
		$this->ip = getip();
		$this->get =  $get;
		$this->post = $post;
		$this->init_db();
		$this->init_cache();
		$this->init_user();
	}

	/* 
	$eventlist=$this('event')->findAll(); 
	本特性只在PHP 5.3.0 及以上版本有效。 
	*/
	function __invoke($modelname, $base = NULL) {
		$base = $base ? $base : $this;
		if (empty($_ENV[$modelname])) {
			$modelfile= APP_ROOT.'/model/'.$modelname.'.class.php';
			//动态创建model类，一般的通用model无需再创建。
			if(false===@include($modelfile)) {
			//	echo $modelname;
				eval('class '.$modelname.'model extends modelbase{}');
			}
		   eval('$_ENV[$modelname] = new ' . $modelname . 'model($base);');
		}
		return $_ENV[$modelname];
	}

	function init_db() {
		//$m= new Mongo('mongodb://'.DB_USER.':'.DB_PW.'@'.DB_HOST.':'.DB_PORT.'/'.DB_NAME);
		//$m= new Mongo('mongodb://'.DB_HOST.':'.DB_PORT.'/'.DB_NAME);
		//启用replicaSet,高可用数据,任意一台关机无所谓。
		// $m = new Mongo("mongodb://10.0.1.227:27017,10.0.1.167:27018,10.0.1.227:27019,10.0.1.167:27020", array("replicaSet" => "mySet"));
		try {
			$m = new Mongo(DB_STR);
			$this->db = $m->selectDB(DB_NAME);
		} catch(MongoConnectionException $e) {
			exit("服务器忙，请稍后重试！");
		}
		//$db->authenticate($username, $password);
		//$this->db =$m->mydata;//选择mydata数据库 ,这种写法也行。
	}
	


	/* 一旦setting的缓存文件读取失败，则更新所有cache */

	function init_cache() {
		global $setting;
		$this->cache = new cache($this->db);
		$setting = $this->setting = $this->cache->load('setting');
		//$this->usergroup = $this->cache->load('usergroup', 'id');
	}

	function init_user() {
		@$auth = tcookie('auth');
		$user = array('uid'=>0);
		@list($uid, $password) = empty($auth) ? array(0, 0) : taddslashes(explode("\t", strcode($auth, $this->setting['auth_key'], 'DECODE')), 1);
		if ($uid && $password) {
			$finduser = $this('user')->findById(intval($uid));
			($password == $finduser['password']) && $user = $finduser;
		}
		$user['ip'] = $this->ip;
		$this->user = $user;
	}

	/* 权限检测 */
	function checkable($regular) {
		return 1;
		$regulars = explode(',', 'user/login,user/logout,user/code,user/getpass,user/resetpass,index/help,js/view,' . $this->user['regulars']);
		return in_array($regular, $regulars);
	}


	/*中转提示页面
	  $ishtml=1 表示是跳转到静态网页
	 */
	function message($message, $url = '') {
		$seotitle = '操作提示';
		if ('' == $url) {
			$redirect = SITE_URL;
		} else if ('BACK' == $url) {
			$redirect = $url;
		} else {
			$redirect = SITE_URL . $this->setting['seo_prefix'] . $url;
		}
		$tpldir = (0 === strpos($this->get['c'], 'admin')) ? 'admin' : $this->setting['tpl_dir'];
		include template('tip', $tpldir);
		exit;
	}

	
	/*提示跳转专用*/
  /*  function theader($key='succeed',$value=1){
		header('location:?c='.$this->get['c'].'&a='.$this->get['a'].'&'.$key.'='.$value);
		exit;
	} 
*/

	/* 检查验证码 */
	function checkcode() {
		if (strtolower(trim($this->post['code'])) != $_SESSION['code']) {
			$this->message("验证码错误!", 'BACK');
		}
	}

 

 
}