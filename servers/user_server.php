<?php
	/**
	* 用户数据
	*/
	class user_server{
		/**
			string 用户表
		*/
		const table='@%_users';
		/**
			string 用户扩展用户组记录表。
		*/
		const egroup_table='@%_user_egroup';
		public function __construct($system){
			$this->system=$system;
		}
		/**
			此方法用于返还用户信息（不含扩展用户组）
			@uid int 用户uid
			return array 用户信息（用户不存在则返回空数组）
				【用户uid（uid）、用户名（username）、邮箱（email）、注册时间（reg_time），注册ip（reg_ip）等】
		*/
		public function get_user_info($uid){
			$res=$this->system->db()->u_exec('SELECT 
				`u`.`uid`,`u`.`username`,`u`.`email`,`u`.`reg_time`,`u`.`reg_ip`,`u`.`sex`,`u`.`signature`,`u`.`title`,`u`.`true_name`,`u`.`qq`,`u`.web,
				`g`.`gname`,`g`.`read_rank`,`g`.`color`,`g`.`use_honor`,`g`.`max_signature`,`g`.`use_title`
				FROM `'.self::table.'` AS `u` LEFT JOIN `'.group_server::table.'` AS `g` ON `u`.`gid`=`g`.`gid` WHERE `u`.`uid`=?',array($uid));
			//var_dump($res);
				return $res?$res[0]:array();
		}
		public $message_type=array('已删除','未读','已读');
		/**
			此方法用于通过uid获取用户名
			因为暂时没有多少用处，暂未制作
		*/
		public function get_usernme_by_uid($uid){
			//
		}
		/**
			此方法用于返回用户的扩展用户组
			@uid int 用户的uid
			return array 扩展用户组数组
		*/
		public function get_egroups($uid){
			$uid+=0;
			return $this->system->db()->exec('SELECT `g`.`gid`,`g`.`gname`,`g`.`color` FROM `'.self::egroup_table.'` AS `u` JOIN `'.group_server::table.'` AS `g` ON `u`.`gid`=`g`.`gid` WHERE `u`.`uid`='.$uid);
		}
		/**
			此方法用于返回用户最近5次登陆记录
			@uid int 用户uid
			return array 登陆信息数组【登陆时间（time）、登陆ip（ip）、是否登陆成功（is_true）】
		*/
		public function get_history_login($uid){
			return $this->system->db()->u_exec('SELECT `ip`,`time`,`is_true` FROM `'.login_server::log_table.'` WHERE uid=? ORDER BY `time` DESC limit 0,5',array($uid));
		}

		/**
			此方法用于获取邀请码可选择的uid（选号功能）
			@key string 邀请码
			return array 授权的uid、用户组等信息数组
		*/
		public function get_uids_by_reg_key($key){
			$db=$this->system->db();
			$temp=$db->u_exec('SELECT `uids`.`uid`,`group`.`gname`
				FROM `@%_uids` LEFT JOIN `group` on `uids`.`group`=`group`.`gid`
				WHERE `uids`.`key`=?',array($key));
			foreach ($temp as &$v) {
				$t=$db->exec('SELECT `username` FROM `'.self::table.'` WHERE `uid`='.$v['uid'].' LIMIT 1');
				if($t){
					echo 'jinlaile';
					$v['isused']=1;
					$db->exec('DELETE FROM `@%_uids` WHERE `uid`='.$v['uid']);
				}
			}
			return $temp;
		}
		/**
			增加一个用户（注册）
			@username string 用户名
			@password string 密码
			@uid int 指定uid（传入null表示不指定）
			@gid int 指定用户组
			return int 结果代码
		*/
		public function add_user($username,$password,$uid=null,$gid=16){
			if(!$gid) $gid=16;
			$db=$this->system->db();
			if($db->u_exec('SELECT `uid` FROM `'.self::table.'` WHERE `username`=? LIMIT 1',array($username))){
				return 1;//用户名被占用
			}
			if($uid&&$db->u_exec('SELECT `username` FROM `'.self::table.'` WHERE `uid`=? LIMIT 1',array($uid))){
				return 2;//uid被占用
			}
			$s=$this->system->rand(6);
			$db->u_exec('INSERT INTO `'.self::table.'`(`uid`,`username`,`password`,`s`,`gid`,`reg_time`,`reg_ip`) VALUES(?,?,?,?,?,?,?)',array($uid,$username,sha1(sha1($password).$s),$s,$gid,time(),$_SERVER['REMOTE_ADDR']));
			return 0;
		}
		/**
			此方法为邀请码注册的简单封装
			@username string 用户名
			@password string 密码
			@uid int 指定uid
			@key string 邀请码
			return int 结果代码
		*/
		public function add_user_for_key($username,$password,$uid,$key){
			$db=$this->system->db();
			$gid=$db->u_exec('SELECT `group` FROM `@%_uids` WHERE `uid`=? AND `key`=?',array($uid,$key));
			if(array_key_exists(0,$gid)){
				return $this->add_user($username,$password,$uid,$gid[0]['group']);
			}else{
				return 'key和uid不匹配';
			}
		}
		/**
			此方法用于匹配邀请码
			@key string 邀请码
			return mix（邀请买不存在返回false|存在则返回信息数组【允许自增长uid（auto）、可用特殊组（groups）】）
		*/
		public function match_reg_key($key){
			$this->system->db()->exec('DELETE FROM `@%_reg_keys` WHERE `end_time`<'.time().' OR `number`<1');
			$res=$this->system->db()->u_exec('SELECT `auto`,`groups` FROM `@%_reg_keys` WHERE `key`=?',array($key));
			if($res){
				return $res[0];
			}else{
				return false;
			}
		}
		//返回一个整数（int），0表示无故障，1用户名重复，2表示uid有问题,3表示无匹配邀请码,4不允许的用户组
		public function reg_key($key,$uid,$usernam,$password,$group){
			$db=$this->system->db();
			$db->exec('DELETE FROM `reg_keys` WHERE `end_time`<'.time().' OR `number`<1');
			$res=$db->u_exec('SELECT `number`,`auto`,`groups` FROM `@%_reg_keys` WHERE `key`=?',array($key));
			if(!$res) return 3;
			if($group){
				$uids=unserialize($res[0]['groups']);
				if(@$uid[$group]<=0){
					return 4;
				}
			}
			if($uid=='auto'){
				if(!$res[0]['auto']){
					return 2;
				}else{
					//echo $username;
					if($t=$this->add_user($usernam,$password,null,$group)){
						return $t;
					}
					//return 0;
				}
			}else{
				return 2;
			}
			$db->u_exec('UPDATE `@%_reg_keys` SET `number`=`number`-1 WHERE `key`=?',array($key));
			return 0;
		}
		//生成一个邀请码
		public function set_reg_key($key=10,$end_time=86400,$number=1,$auto=1){
			if(is_int($key)&&$key<200){
				$key=$this->system->rand($key);
			}
			$db=$this->system->db();
			//判断key是否已经存在
			if($db->u_exec('SELECT `key` FROM `@%_reg_keys` WHERE `key`=?',array($key))){
				return false;
			}
			//修正number
			if($number<=0){
				$number=1;
			}
			//
			if($end_time<=3600*24*10){
				$end_time+=time();
			}
			$db->u_exec('INSERT INTO `@%_reg_keys`(`key`,`end_time`,`number`,`auto`) VALUES(?,?,?,?)',array($key,$end_time,$number,$auto));
			return $key;
		}
		public function get_reg_key_list(){
			return $this->system->db()->exec('SELECT * FROM `@%_reg_keys`');
		}
		public function get_users_list(){
			return $this->system->db()->exec('SELECT `uid`,`username`,`sex`,`reg_time`,`reg_ip` FROM `'.self::table.'` where `username` IS NOT NULL');
		}

		public function change_password($uid,$new_password){
			$s=$this->system->rand(6);
			$this->system->db()->u_exec('UPDATE `'.self::table.'` SET `password`=?,`s`=? WHERE `uid`=?',array(sha1(sha1($new_password).$s),$s,$uid));
		}
	}
?>