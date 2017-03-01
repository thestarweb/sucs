<?php
class admin_server{
	private $admin_table='@%_admins';
	private $page_table='@%_admin_pages';
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	public function get_list($id,$uid=0){
		$id+=0;
		if($uid==0){
			$res=$this->system->db()->exec('SELECT `id`,`title`,`is_menu`,`src` FROM `'.$this->page_table.'` WHERE `pid`='.$id);
		}else{
			$uid+=0;//echo $uid.'--';exit;
			$res=$this->system->db()->exec('SELECT `page`.`id`,`page`.`title`,`page`.`is_menu`,`page`.`src` FROM `'.$this->page_table.'` AS `page` JOIN `'.$this->admin_table.'` AS `admin` ON `page`.`id`=`admin`.`pid` WHERE `page`.`pid`='.$id.' AND `admin`.`uid`='.$uid);
	//	var_dump($res);
		}
		foreach($res as &$v){
			if(!$v['src'])$v['src']=URLROOT.'myadmin/view?id='.$v['id'];
		}
		return $res;
	}
	public function can_visit($uid,$id){
		if($uid===false) return false;
		if($uid==0){
			if($id==0)return 1;
			$res=$this->system->db()->exec('SELECT `is_menu` FROM `'.$this->page_table.'` AS `p` WHERE `id`='.$id);
			if($res){
				return $res[0]['is_menu']+1;
			}
		}else{
			$uid+=0;$id+=0;
			if($id==0){
				return $this->system->db()->exec('SELECT `id` FROM `'.$this->admin_table.'` WHERE `uid`='.$uid.' LIMIT 1')?2:false;
			}
			$res=$this->system->db()->exec('SELECT `p`.`is_menu` FROM `'.$this->admin_table.'` AS `a` JOIN `'.$this->page_table.'` AS `p` ON `a`.`pid`=`p`.`id` WHERE `a`.`uid`='.$uid.' AND `p`.`id`='.$id);
			if($res){
				return $res[0]['is_menu']+1;
			}
		}
		return false;
	}
	//权限 1管理员赋予 2继承得到 3连接作用
	public function flash($uid){
		if($uid==0) return true;
		$uid+=0;
		$db=$this->system->db();
		//清除旧记录
		$db->exec('UPDATE `'.$this->admin_table.'` SET `type`=0 WHERE `type`!=1 AND `uid`='.$uid);
		//查询已经授权的列
		$res=$db->exec('SELECT `a`.`pid` AS `id`,`p`.`is_menu` FROM `'.$this->admin_table.'` AS `a` LEFT JOIN `'.$this->page_table.'` AS `p` ON `a`.`pid`=`p`.`id` WHERE `a`.`type`=1 AND `a`.`uid`='.$uid);
		$list=array();$new=array();
		//需要的连接
		var_dump($res);
		foreach($res as $v){
			//$this->add($uid,$v['pid'],3);
			if($v['is_menu']||$v['id']==0) array_push($list,$v);
		}
		//遍历所以子项
		var_dump($list);
		while($item=array_pop($list)){
			$res=$db->exec('SELECT `id`,`is_menu` FROM `'.$this->page_table.'` WHERE `pid`='.$item['id']);
			foreach($res as $v){
				//$this->add($uid,$v['id'],2);
				array_push($new,array($v['id'],2));
				if($v['is_menu']) array_push($list,$v);
			}
		}
		//写入新记录
		$res=$db->exec('SELECT `id` FROM `'.$this->admin_table.'` WHERE `type`=0 LIMIT '.count($new));
		while($id=array_pop($res)){
			$insert=array_pop($new);
			$db->exec('UPDATE `'.$this->admin_table.'` SET `uid`='.$uid.',`pid`='.$insert[0].',`type`='.$insert[1].' WHERE `id`='.$id['id']);
		}
		$str='INSERT INTO `'.$this->admin_table.'`(`uid`,`pid`,`type`) VALUE';
		if($new){
			$f=false;
			foreach($new as $v){
				if($f){
					$str.=',';
				}else{
					$f=true;
				}
				$str.='('.$uid.','.$v[0].','.$v[1].')';
			}
			$db->exec($str);
		}
	}
	public function add($uid,$pid,$type=1){
		$uid+=0;$pid+=0;$type+=0;
		//$this->system->db()->exec('INSERT INTO `'.$this->admin_page.'`(`uid`,`pid`,`type`) VALUE(logologo
	}
}