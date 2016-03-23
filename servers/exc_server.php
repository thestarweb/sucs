<?php
/**
* 用户积分
*/
class exc_server{
	const table='@%_user_exc';
	const log_table='@%_exc_log';
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
		return $bank;
	}
	public function add($uid,$name,$d,$why=''){//小心！！使用此函数必须保证$name 的安全性
		$uid+=0;$d+=0;
		if(!$this->system->db()->do_SQL('UPDATE `'.self::table.'` SET `'.$name.'`=`'.$name.'`+'.$d.' WHERE `uid`='.$uid)){
			$this->system->db()->do_SQL('INSERT INTO`'.self::table.'`(`uid`) VALUE('.$uid.')');
			$this->system->db()->do_SQL('UPDATE `'.self::table.'` SET `'.$name.'`=`'.$name.'`+'.$d.' WHERE `uid`='.$uid);
		}
		$this->system->db()->u_do_SQL('INSERT INTO `'.self::log_table.'`(`timeid`,`uid`,`name`,`number`,`why`) VALUE(?,?,?,?,?)',array(time().rand(10000,99999),$uid,$name,$d,$why));
	}
	public function add_s($uid,$excid,$d,$why){//有分析表中字段验证的方案,相对来说安全得多
		$list=$this->get_all_info();
		if(isset($list[$excid])) $this->add($uid,$list[$excid]['name'],$d,$why);
	}
	public function add_for_login($uid){
		$this->add_s($uid,0,5,'登陆奖励');
	}
	public function read_log($uid){
		$uid+=0;
		$res=$this->system->db()->do_SQL('SELECT `name`,`number`,`why` FROM `'.self::log_table.'`WHERE `isread`=0 && `uid`='.$uid);
		if($res)$this->system->db()->do_SQL('UPDATE `'.self::log_table.'` SET `isread`=1 WHERE `uid`='.$uid);
		return $res;	
	}
}