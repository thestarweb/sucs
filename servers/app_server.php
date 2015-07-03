<?php
class app_server{
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	public function is_admit($app_id,$p_time,$md5,$data){
		if($p_time<(time()-60)){//超时请求
			return false;
		}
		$app_id+=0;
		$res=$this->system->db()->do_SQL('SELECT `urlroot`,`key` FROM `sucs` WHERE `aid`='.$app_id.' LIMIT 1');
		if($res){
			$str=$p_time;
			foreach ($data as $v){
				$str.=$v;
			}
			$str.=$res[0]['key'];
			if(md5($str)=$md5) return true;
		}
		return false;
	}
}