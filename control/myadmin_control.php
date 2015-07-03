<?php
class myadmin_control{
	public function __construct($system){
		$login=new login_server($system);
		$this->uid=$login->is_login();
		//var_dump($this);exit;
		$this->admin=new admin_server($system);
		//$this->admin->flash(1);exit;
		if(!$t=$this->type=$this->admin->can_visit($this->uid,isset($_GET['id'])?$_GET['id']:0)){
			//var_dump($t,$this->uid);
			require $system->get_view('admin/login');
			exit();
		}
	}
	public function index_page($system){
		//$admin=new admin_server($system);
		$menus=$this->admin->get_list(0,$this->uid);
		require $system->get_view('admin/index');
	}
	public function list_page($system){
			$system->show_json(array('list'=>$this->admin->get_list(2,$this->uid)));
	}
	public function view_page($system){
		//var_dump($this);
		if($this->type==1){
		if(file_exists($file=$system->ini_get('controls_dir').'admin_file/'.$_GET['id'].'.php')){
			include_once $file;
		}else{
			echo '页面丢失'.$file=$system->ini_get('controls_dir').'admin_file/'.$_GET['id'].'.php';
		}
		}else{
			$this->list_page($system);
		}
	}
}