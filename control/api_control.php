<?php
class api_control{
	public function __construct($system){
		//$this->app=app_server($system);
		//if(!empty($_GET['app_id'])||!$this->app->star($_GET['app_id'],$_GET['time'])) $system->show_json(array('error'=>'无效的应用'));
		header('Access-Control-Allow-Origin: http://blog.thestarweb.sweb');
		header('Access-Control-Allow-Credentials:true');
	}
	/*public function get_key_page($system){
		if(true){
			die('非法访客');
		}
		$login=login_server($system);
		if($login->is_login===false){
			$system->show_json(array('info'=>''));
		}else{
			$system->show_json(array('info'=>array('key'=>$_COOKIE['r_login_key'],'id'=>$_COOKIE['r_login_key'])));
		}
	}
	public function is_login_page($system){
		if($this->app->is_user($_GET['login_key'],$_GET['key'])){
			$system->show_json(array('is_login'=>1));
		}else{
			$system->show_json(array('is_login'=>0));
		}
	}
	public function add_exp_page($system){
		if($this->app->add_exp($_GET['login_key'],$_GET['key'])){
			$system->show_json(array('is_ok'=>1));
		}else{
			$system->show_json(array('is_ok'=>0));
		}
	}
	public function set_exp_page($system){
		if($this->app->set_exp($_GET['login_key'],$_GET['key'])){
			$system->show_json(array('is_ok'=>1));
		}else{
			$system->show_json(array('is_ok'=>0));
		}
	}*/
	public function index_page($system){
		//header("Content-type:text/javascript");
		$login=new login_server($system);
		$system->show_json(array('u'=>$login->get_now_user()));
	}
}