<?php
class app_server{
	const table='@%_apps';
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	public function is_admit($data=''){
		if(!isset($_POST['p_time'])||!isset($_POST['APP_id'])||!isset($_POST['md5'])||$_POST['p_time']<(time()-30)){//超时请求
			return false;
		}
		$_POST['APP_id']+=0;
		$res=$this->system->db()->do_SQL('SELECT `urlroot`,`key` FROM `'.self::table.'` WHERE `aid`='.$_POST['APP_id'].' LIMIT 1');
		if($res){
			header('Access-Control-Allow-Origin: '.$res[0]['urlroot']);
			header('Access-Control-Allow-Credentials:true');
			$str=$_POST['p_time'].$data.$res[0]['key'];
			if(md5($str)==$_POST['md5']) return true;
		}
		return false;
	}
	public function add($name,$urlroot){
		$this->system->db()->u_do_SQL('INSERT INTO `'.self::table.'`(`name`,`urlroot`,`key`) VALUE(?,?,?)',array($name,$urlroot,$this->system->rand(11)));
	}
	public function get_list(){
		return $this->system->db()->do_SQL('SELECT `aid`,`name`,`urlroot`,`key` FROM `'.self::table.'`');
	}
}