<?php
class message_server{
	const table='@%_message';
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	/**
		获取消息列表
		@uid int 用户id
		@oney_no_resd bool 是否只显示未读消息
		@no_self bool 是否隐藏自己发出的消息
		return array 包含消息id，消息发送人、消息接收人、发送时间等信息的二维数组
	*/
	public function get_message_list($uid,$oney_no_resd=false,$no_self=false){
		//return $this->system->db()->u_exec('SELECT `u`.`username` as `sender`,`m`.`time`,`m`.`body`,`m`.`mid` FROM `message` AS `m` JOIN `users` AS `u` ON `m`.`sender`=`u`.`uid` WHERE `m`.`geter`=?'.($oney_no_resd?' AND `m`.`is_get`=0':''),array($uid));
		$res=$this->system->db()->u_exec('SELECT `m`.`mid`,`m`.`body`,`m`.`time`,`u_g`.`username` AS `geter`,`m`.`type`,`u_s`.`username` AS `sender`,`m`.`geter` AS `geter_id`,`m`.`sender` AS `sender_id`
			FROM `'.self::table.'` AS `m` JOIN `'.user_server::table.'` AS `u_g` ON `m`.`geter`=`u_g`.`uid` JOIN `users` AS `u_s` ON `m`.`sender`=`u_s`.`uid`
			WHERE `m`.`geter`=? '.($no_self?'AND `type`&2=0 ':'OR `m`.`sender`=? ').($oney_no_resd?'AND `m`.`type`&1=0':'').' ORDER BY `m`.`time` desc',$no_self?array($uid):array($uid,$uid));
		if(!$no_self){
			foreach ($res as $k=>$v){
				if($v['geter_id']===$uid){
					if($v['type']&2) unset($res[$k]);
				}else{
					if($v['type']&4) unset($res[$k]);
				}
			}
		}
		return $res;
	}
	/**
		获取一条消息（不带可读验证）
		@mid int 消息id
		return mix 消息存在为消息信息数组，不存在则返回false
	*/
	public function get_message($mid){
		//$res=$this->system->db()->u_exec('SELECT `u`.`uid` as `senderid`,`u`.`username` as `sender`,`m`.`time`,`m`.`body`,`m`.`geter` FROM `message` AS `m` JOIN `users` AS `u` ON `m`.`sender`=`u`.`uid` WHERE `m`.`mid`=?',array($mid));
		$res=$this->system->db()->u_exec('SELECT `m`.`mid`,`m`.`body`,`m`.`time`,`u_g`.`username` AS `geter`,`u_g`.`uid` AS `geter_id`,`m`.`type`,`u_s`.`username` AS `sender`,`u_s`.`uid` AS `sender_id`
			FROM `'.self::table.'` AS `m` JOIN `'.user_server::table.'` AS `u_g` ON `m`.`geter`=`u_g`.`uid` JOIN `'.user_server::table.'` AS `u_s` ON `m`.`sender`=`u_s`.`uid`
			WHERE `m`.`mid`=? LIMIT 1',array($mid));
		if($res){
			return $res[0];
		}else{
			return false;
		}
	}
	/**
		标记一条消息为已读
		@id int 消息id
		return 执行结果
	*/
	public function set_message_is_red($id){
		$id+=0;
		return $this->system->db()->exec('UPDATE `'.self::table.'` SET `type`=`type`|1 WHERE `mid`='.$id);
	}
	/**
		发送消息
		@sender int 发送者uid
		@body string 消息正文
		@geter int 接收人uid
		return void
	*/
	public function send_message($sender,$body,$geter){
		$this->system->db()->u_exec('INSERT INTO `'.self::table.'`(`sender`,`time`,`body`,`geter`) VALUES(?,?,?,?)',array($sender,time(),htmlentities($body,ENT_NOQUOTES,'utf-8'),$geter));
		//
	}
	/**
		标记删除一条消息
		@mid int 消息id
		@t char s=发送人删除 g=接收人删除
	*/
	public function delete($mid,$t){
		$mid+=0;
		$this->system->db()->exec('UPDATE `'.self::table.'` SET `type`=`type`|'.($t=='s'?4:2).' WHERE `mid`='.$mid);
	}
	/**
		判断消息是否可被指定用户阅读（不包含管理员查看判断）
		@mess array 消息信息数组
		@uid int 需要判断的用户uid
		return mix s=发送人阅读 g=接收人阅读 false(bool)=不可阅读
	*/
	public function is_true_message($mess,$uid){
		if($mess['geter_id']===$uid){
			if(!($mess['type']&2)) return 'g';
		}elseif($mess['sender_id']===$uid){
			if(!($mess['type']&4)) return 's';
		}
		return false;
	}
}
