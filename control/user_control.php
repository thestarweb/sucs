<?php
class user_control{
	public function head_page($system,$uid){
		header('Content-Type: image/jpeg');
		//var_dump(file_exists($img=$system[0]->ini_get('imgs_dir').'u_head/0.jpg'));exit;
		if(($uid+=0)&&file_exists($img=$system->ini_get('imgs_dir').'u_head/'.$uid.'.jpg')){
			echo file_get_contents($img);
			exit;
		}
		echo file_get_contents($system->ini_get('imgs_dir').'u_head/0.jpg');
	}
	public function info_page($system,$uid){
		$uid+=0;
		$user_server=new user_server($system);
		$user=$user_server->get_user_info($uid);
		if(!$user){
			echo 404;exit;
		}
		$system->show_head($user['username'].'的信息');
		$exc_server=new exc_server($system);
		$exc=$exc_server->get_by_uid($uid);
		include $system->get_view('user_info');
		$system->show_foot();
	}
	public function card_page($system,$uid){
		$uid+=0;
		$user_server=new user_server($system);
		$user=$user_server->get_user_info($uid);
		if($user){
			include $system->get_view('card',false);
		}
	}
}