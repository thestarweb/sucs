<?php
/**
* 用户积分
*/
class exc_server{
	const table='@%_user_exc';
	public function __construct($system){
		$this->system=$system;
	}
	public function get_by_uid($uid){
		$uid+=0;
		$res=$this->system->db()->do_SQL('SELECT * FROM `'.self::table.'` WHERE `uid`='.$uid);
		if(!$res){
			//if($this->system->db()->do_SQL('SELCET * FROM `users` WHERE `uid`='.$uid)){
			$this->system->db()->do_SQL('INSERT INTO `user_exc`(`uid`) VALUE('.$uid.')');
			$res=$this->system->db()->do_SQL('SELECT * FROM `'.self::table.'` WHERE `uid`='.$uid);
			//}
		}
		unset($res[0]['uid']);
		return $res[0];
	}
	public function get_all_info(){
		$res=$this->system->db()->do_SQL('DESC `'.self::table.'`');
		unset($res[0]);
		$bank=array();
		//var_dump($res);exit;
		foreach($res as $v){
			$bank[]=array('name'=>$v['Field'],'value'=>$v['Default']);
		}
		var_dump($bank);
	}
	public function add($uid,$name,$d){//小心！！使用此函数必须保证$name 的安全性
		$uid+=0;$d+=0;
		if(!$this->system->db()->do_SQL('UPDATE `'.self::table.'` SET `'.$name.'`=`'.$name.'`+'.$d.' WHERE `uid`='.$uid)){
			$this->system->db()->do_SQL('INSERT INTO`'.self::table.'`(`uid`) VALUE('.$uid.')');
		}
	}
}