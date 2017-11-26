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
	/**
		获取用户的积分信息
		@uid int 用户id
		return array 包含积分信息的数组
	*/
	public function get_by_uid($uid){
		$uid+=0;
		$res=$this->system->db()->exec('SELECT * FROM `'.self::table.'` WHERE `uid`='.$uid);
		if(!$res){
			//if($this->system->db()->exec('SELCET * FROM `users` WHERE `uid`='.$uid)){
			$this->system->db()->exec('INSERT INTO `user_exc`(`uid`) VALUE('.$uid.')');
			$res=$this->system->db()->exec('SELECT * FROM `'.self::table.'` WHERE `uid`='.$uid);
			//}
		}
		unset($res[0]['uid']);
		return $res[0];
	}
	/**
		获取积分信息的二维数组（有哪些积分、默认值为多少）
	*/
	public function get_all_info(){
		$res=$this->system->db()->exec('DESC `'.self::table.'`');
		unset($res[0]);
		$bank=array();
		//var_dump($res);exit;
		foreach($res as $v){
			$bank[]=array('name'=>$v['Field'],'value'=>$v['Default']);
		}
		return $bank;
	}
	/**
		为用户增加积分（设置负数可扣除积分）
		@uid int 用户uid
		@name string 积分名称（需要在函数外检测其安全性已避免SQL注入）
		@d int 增加数量
		@why string 操作原因
	*/
	public function add($uid,$name,$d,$why=''){//小心！！使用此函数必须保证$name 的安全性
		$uid+=0;$d+=0;
		if(!$last=$this->system->db()->exec('SELECT `'.$name.'` FROM `'.self::table.'` WHERE `uid`='.$uid)){
			$this->system->db()->exec('INSERT INTO`'.self::table.'`(`uid`) VALUE('.$uid.')');
			$last=$this->system->db()->exec('SELECT `'.$name.'` FROM `'.self::table.'` WHERE `uid`='.$uid);
		}
		if($last[0][$name]+$d<0){
			return 0;
		}
		$this->system->db()->exec('UPDATE `'.self::table.'` SET `'.$name.'`=`'.$name.'`+'.$d.' WHERE `uid`='.$uid);
		$this->system->db()->u_exec('INSERT INTO `'.self::log_table.'`(`timeid`,`uid`,`name`,`number`,`why`) VALUE(?,?,?,?,?)',array(time().rand(10000,99999),$uid,$name,$d,$why));
		return 1;
	}
	/**
		上一个函数的安全版本，excid可根据顺序来找到积分名称，也可验证积分名称（不可使任何形式的数字）。
		如果数据库中本就包含了奇怪的符号那它就帮不了你了
	*/
	public function add_s($uid,$excid,$d,$why){//有分析表中字段验证的方案,相对来说安全得多
		$list=$this->get_all_info();
		if(isset($list[$excid])) return $this->add($uid,$list[$excid]['name'],$d,$why);
		else foreach($list as $v){
			if($v['name']==$excid) return $this->add($uid,$excid,$d,$why);
		}
		return 0;
		//if(in_array($, haystack)) return $this->add($uid,$list[$excid]['name'],$d,$why);
	}
	/**
		增加登陆奖励
		@uid int 用户id
	*/
	public function add_for_login($uid){
		$this->add_s($uid,'经验',5,'登陆奖励');
		$this->add_s($uid,'铜钱',10,'登陆奖励');
	}
	/**
		获取最近（还未查看的）积分变化
		@uid int 用户id
		return array 积分变化信息的二维数组
	*/
	public function read_log($uid){
		$uid+=0;
		$res=$this->system->db()->exec('SELECT `name`,`number`,`why` FROM `'.self::log_table.'`WHERE `isread`=0 && `uid`='.$uid);
		if($res)$this->system->db()->exec('UPDATE `'.self::log_table.'` SET `isread`=1 WHERE `uid`='.$uid);
		return $res;	
	}
}