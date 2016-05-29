<?php
class shop_server{
	public function __construct($system){
		$this->system=$system;
	}
	const table='@%_goods';
	public function get_public_list(){
		return $this->system->db()->do_SQL('SELECT `gid`,`name`,`jiesao`,`take_type`,`take_number` FROM `'.self::table.'` WHERE `need_type`=0');
	}
	public function goods_info($id){
		$id+=0;
		$res=$this->system->db()->do_SQL('SELECT `name`,`jiesao`,`take_type`,`take_number` FROM `'.self::table.'` WHERE `gid`='.$id);
		return $res?$res[0]:array('name'=>'','jiesao'=>'','take_type'=>'','take_number'=>'');
	}
	/**
	@gid 商品id
	@uid 购买用户id
	@num 购买数量
	@return int :
		-2 商品购买脚本丢失
		-1 商品不存在
		0 购买成功
		1 余额不足
		2 无权限购买
		3 参数有误
	*/
	public function buy($gid,$uid,$num){
		$gid+=0;$uid+=0;$num+=0;
		if($num<1||is_float($num)) return 3;
		$g_info=$this->system->db()->do_SQL('SELECT `name`,`take_type`,`take_number`,`need_type` FROM `'.self::table.'` WHERE `gid`='.$gid);
		if($g_info){
			$g_info=$g_info[0];
			switch ($g_info['need_type']) {
				case 0:
					$bfile=$this->system->ini_get('controls_dir').'goods/by_'.$gid.'.php';
					if(!file_exists($bfile)) return -2;
					$exc=new exc_server($this->system);
					if($exc->add_s($uid,$g_info['take_type'],-$g_info['take_number']*$num,'购买'.$g_info['name'].$num.'个')){
						include $bfile;
						return 0;
					}else{
						return 1;
					}
					break;
				
				default:
					return 2;
					break;
			}
		}
		return -1;
	}
}