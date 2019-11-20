<?php
class succ{
	//cons table='succ`.`users`';
	public static $self_obj=null;
	public static function get_obj(){
		if(!self::$self_obj) self::$self_obj=new succ();
		return self::$self_obj;
	}
	public $cooke_pass='/';
	public $cooke_name='succ_key';
	public $link_use;
	private $js_key;
	public function get_js_key(){
		return $this->js_key;
	}
	private $appid;
	private $appkey;
	public function set_app_info($i,$k){
		$this->appid=$i;
		$this->appkey=$k;
	}
	public function __construct(){
		require_once __DIR__.'/usedb.php';$this->link_use=new db_link();
		$this->js_key=rand(100,999);
	}
	public function set_system($system){
		$this->link_use->set_system($system);
	}
	public function login_page(){
		return $this->link_use->server_url.'/index/login';
	}
	public function head_html($uid){
		include __DIR__.'/view/user_head.html';
	}
	public function show_script(){
		include __DIR__.'/view/main_script.html';
	}
	public function is_login($key,$UA){
		return $this->link_use->islogin($key,$UA);
	}
	public function set_key($key){
		//setcookie($this->cooke_name,$key);//暂时没有多少作用，注释掉
	}
}