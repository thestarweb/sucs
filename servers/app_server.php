<?php
namespace sucs; 
class app_server{
	const table='@%_apps';
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	/**
		检查调用是否合法
		@data string 额外数据
		return bool
	*/
	public function is_admit($data=''){
		if(!isset($_POST['p_time'])||!isset($_POST['APP_id'])||!isset($_POST['hash'])||$_POST['p_time']<(time()-30)){//超时请求
			return false;
		}
		$_POST['APP_id']+=0;
		$res=$this->system->db()->exec('SELECT `urlroot`,`key` FROM `'.self::table.'` WHERE `aid`='.$_POST['APP_id'].' LIMIT 1');
		if($res){
			$str=$_POST['p_time'].$data.$res[0]['key'];
			if(hash('sha256',$str)==$_POST['hash']){
				// if(isset($_SERVER['HTTP_REFERER'])){
				// 	header('Access-Control-Allow-Origin: '.substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],'/',8)));
				// 	header('Access-Control-Allow-Credentials:true');
				// }
				$t=explode('//',$res[0]['urlroot'],2);
				if(isset($t[1])){
					$t=explode('/',$t[1]);
					header('Access-Control-Allow-Origin: '.$_SERVER["REQUEST_SCHEME"].'://'.$t[0]);
				 	header('Access-Control-Allow-Credentials:true');
				}
				return true;
			}
		}
		return false;
	}
	/**
		增加一个应用（可访问程序）
		@name string 应用名称
		@urlroot string 应用网站根目录
	*/
	public function add($name,$urlroot){
		$this->system->db()->u_exec('INSERT INTO `'.self::table.'`(`name`,`urlroot`,`key`) VALUE(?,?,?)',array($name,$urlroot,$this->system->rand(11)));
	}
	/**
		获取应用列表
	*/
	public function get_list(){
		return $this->system->db()->exec('SELECT `aid`,`name`,`urlroot`,`key` FROM `'.self::table.'`');
	}
}