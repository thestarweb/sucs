<?php
class market_server{
	public function __construct($system){
		$this->system=$system;
	}
	const table='@%_goods';
	public function get_public_list(){
		return $this->system->db()->do_SQL('SELECT `gid`,`name`,`jiesao`,`take_type`,`take_number` FROM `'.self::table.'` WHERE `need_type`=0');
	}
}