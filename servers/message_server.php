<?php
class message_server{
	const table_name='@%_message';
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	public function get_message_list($uid,$oney_no_resd=false,$no_self=false){
		//return $this->system->db()->u_do_SQL('SELECT `u`.`username` as `sender`,`m`.`time`,`m`.`body`,`m`.`mid` FROM `message` AS `m` JOIN `users` AS `u` ON `m`.`sender`=`u`.`uid` WHERE `m`.`geter`=?'.($oney_no_resd?' AND `m`.`is_get`=0':''),array($uid));
		$res=$this->system->db()->u_do_SQL('SELECT `m`.`mid`,`m`.`body`,`m`.`time`,`u_g`.`username` AS `geter`,`m`.`type`,`u_s`.`username` AS `sender`,`m`.`geter` AS `geter_id`,`m`.`sender` AS `sender_id`
			FROM `message` AS `m` JOIN `users` AS `u_g` ON `m`.`geter`=`u_g`.`uid` JOIN `users` AS `u_s` ON `m`.`sender`=`u_s`.`uid`
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
	public function get_message($mid){
		//$res=$this->system->db()->u_do_SQL('SELECT `u`.`uid` as `senderid`,`u`.`username` as `sender`,`m`.`time`,`m`.`body`,`m`.`geter` FROM `message` AS `m` JOIN `users` AS `u` ON `m`.`sender`=`u`.`uid` WHERE `m`.`mid`=?',array($mid));
		$res=$this->system->db()->u_do_SQL('SELECT `m`.`mid`,`m`.`body`,`m`.`time`,`u_g`.`username` AS `geter`,`u_g`.`uid` AS `geter_id`,`m`.`type`,`u_s`.`username` AS `sender`,`u_s`.`uid` AS `sender_id`
			FROM `message` AS `m` JOIN `users` AS `u_g` ON `m`.`geter`=`u_g`.`uid` JOIN `users` AS `u_s` ON `m`.`sender`=`u_s`.`uid`
			WHERE `m`.`mid`=? LIMIT 1',array($mid));
		if($res){
			return $res[0];
		}else{
			return false;
		}
	}
	public function set_message_is_red($id){
		$id+=0;
		return $this->system->db()->do_SQL('UPDATE `message` SET `type`=`type`|1 WHERE `mid`='.$id);
	}
	public function send_message($sender,$body,$geter){
		$this->system->db()->u_do_SQL('INSERT INTO `message`(`sender`,`time`,`body`,`geter`) VALUES(?,?,?,?)',array($sender,time(),htmlentities($body,ENT_NOQUOTES,'utf-8'),$geter));
		//
	}
	public function send_messages($sender,$body,$geters){
		$t=$this->system->db()->prepare('INSERT INTO `message`(`sender`,`time`,`body`,`geter`) VALUES(?,?,?,?)');
		foreach ($geters as $v){
			if($v){
				$t->execute(array($sender,time(),htmlentities($body,ENT_NOQUOTES,'utf-8'),$v));
			}
		}
	}
	public function delete($mid,$t){
		$mid+=0;
		$this->system->db()->do_SQL('UPDATE `'.self::table_name.'` SET `type`=`type`|'.($t=='s'?4:2).' WHERE `mid`='.$mid);
	}
	public function is_true_message($mess,$uid){
		if($mess['geter_id']===$uid){
			if(!($mess['type']&2)) return 'g';
		}elseif($mess['sender_id']===$uid){
			if(!($mess['type']&4)) return 's';
		}
		return false;
	}
}
