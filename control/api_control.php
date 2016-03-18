<?php
class api_control{
	public function __construct($system){
		$this->app=new app_server($system);
		//if(!empty($_GET['app_id'])||!$this->app->star($_GET['app_id'],$_GET['time'])) $system->show_json(array('error'=>'无效的应用'));
	}
	public function is_login_page($system){
		if($this->app->is_admit()){
			$login=new login_server($system);
			$system->show_json(array('u'=>$login->get_now_user()));
		}else{
			echo 'you cant visit a api page without a true key';
		}
	}
	public function message_page($system){
		/*$this->app->is_admit();
		set_time_limit(0);
header(”Connection: Keep-Alive”);
header(”Proxy-Connection: Keep-Alive”);
		$_SESSION['test']=isset($_SESSION['test'])?($_SESSION['test']+1):1;
		for($i=0;$i<5000;$i++){
			echo ' ';
		}print str_repeat(" ", 40960);
		while (true) {
			sleep(5);
			echo rand(10,50).$_SESSION['test'].'<br/>';
			ob_flush();
			flush();
		}*/
	}
	/*public function add_exp_page($system){
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
	}
	/*public function __destruct(){
		$_SESSION['test']-=1;
	}*/
}