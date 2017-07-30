<?php
class shop_server{
	public function __construct($system){
		$this->system=$system;
	}
	/**
		string 商品表
	*/
	const table='@%_goods';
	/**
		此方法用于获取大家都可购买的商品
		return array 商品信息数组
	*/
	public function get_public_list(){
		return $this->system->db()->exec('SELECT `gid`,`name`,`jiesao` AS ginfo,`take_type`,`take_number` FROM `'.self::table.'` WHERE `need_type`=0');
	}
	/**
		此方法用于获取商品信息
		@id int 商品 id
		return array 商品信息数组【商品名称（name）、商品介绍（ginfo）、积分种类（take_type）、积分数量（take_number）】
	*/
	public function goods_info($id){
		$id+=0;
		$res=$this->system->db()->exec('SELECT `name`,`jiesao` AS `ginfo`,`take_type`,`take_number` FROM `'.self::table.'` WHERE `gid`='.$id);
		return $res?$res[0]:array('name'=>'','jiesao'=>'','take_type'=>'','take_number'=>'');
	}
	/**
		此方法用于购买商品
		@gid int 商品id
		@uid int 购买用户id
		@num int 购买数量
		@return int 购买状态码:
			-2 商品购买脚本丢失
			-1 商品不存在
			0 购买成功
			1 余额不足
			2 无权限购买
			3 参数有误
	*/
	public function buy($gid,$uid,$num){
		$gid+=0;$uid+=0;$num+=0;
		if($num<1||is_float($num)) return 1003;
		$g_info=$this->system->db()->exec('SELECT `name`,`take_type`,`take_number`,`need_type` FROM `'.self::table.'` WHERE `gid`='.$gid);
		if($g_info){
			$g_info=$g_info[0];
			switch ($g_info['need_type']) {
				case 0:
					$bfile=$this->system->ini_get('controls_dir').'goods/by_'.$gid.'.php';
					if(!file_exists($bfile)) return 1005;
					$exc=new exc_server($this->system);
					if($exc->add_s($uid,$g_info['take_type'],-$g_info['take_number']*$num,$this->system->lang('home','buy.info',array($num,$g_info['name'])))){
						include $bfile;
						return 0;
					}else{
						return 1001;
					}
					break;
				
				default:
					return 1002;
					break;
			}
		}
		return 1004;
	}
}