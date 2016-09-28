<?php
class db_link{
	public $tink_type='db';
	public $db_server='127.0.0.1';
	public $db_username='sucs';
	public $db_password='sucs';
	public $db_name='sucs';
	public $server_url='http://127.0.0.1:8080/sweb/sucs';//结尾一定不能加斜杠
	public $servers_dir='../servers';
	public $db_prefix='';
	public $_db;
	public function db(){
		if(!$this->_db) $this->_db=new pdo_mysql($this->db_server,$this->db_username,$this->db_password,$this->db_name,$this->db_prefix);
		return $this->_db;
	}
	public function islogin($key,$UA){
		if($key){
			$res=$this->db()->u_do_SQL('SELECT `uid`,`UA` FROM `logins` WHERE `key`=?',array($key));
			if($res&&$UA==$res[0]['UA']){
				return $res[0]['uid'];
			}
		}
		return false;
	}
}