<?php
class submit_safe_server{
	const table='@%_submit_safe';
	const max_time=300;
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	public function is_locked($max){
		$db=$this->system->db();
		$db->exec('DELETE FROM `'.self::table.'` WHERE `time`<'.(time()-self::max_time));
		$res=$db->u_exec('SELECT `num` FROM `'.self::table.'` WHERE `ip`=?',array($this->system->uip()));
		return $res&&($res[0]['num']>$max);
	}
	public function add($num){
		$db=$this->system->db();
		$res=$db->u_exec('UPDATE `'.self::table.'` SET `num`=`num`+'.$num.',`time`=? WHERE `ip`=?',array(time(),$this->system->uip()));
		if(!$res){
			$db->u_exec('INSERT INTO `'.self::table.'`(`ip`,`time`,`num`) VALUE(?,?,?)',array($this->system->uip(),time(),$num));
		}
	}
}