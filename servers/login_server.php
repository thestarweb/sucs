<?php
	class login_server{
		public $system;
		const table='@%_logins';
		const log_table='@%_login_log';
		const re_table='@%_login_remember';
		public function __construct($system){
			$this->system=$system;
		}
		public function add_login($uid,$username=0,$is_ok=1){
			$db=$this->system->db();
			if($username===0){
				$res=$db->do_SQL('SELECT `username` from `users` WHERE `uid`='.$uid);
				$username=$res[0]['username'];
			}
			//写入登陆日志
			$db->u_do_SQL('INSERT INTO `'.self::log_table.'`(`time`, `ip`, `uid`, `username`, `HTTP_USER_AGENT`, `is_true`) VALUES(?,?,?,?,?,?)',
				array(time(),$_SERVER['REMOTE_ADDR'],$uid,$username,$_SERVER['HTTP_USER_AGENT'],$is_ok));
			//完成登陆
			if($is_ok){
				//
				$str='ABCDEGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
				$str.=$str;
				$key=substr(str_shuffle($str),0,10);
				setcookie('login_key',$key,0,URLROOT);
				$_COOKIE['login_key']=$key;
				$db->u_do_SQL('INSERT INTO `'.self::table.'`(`key`,`time`,`uid`,`username`,`UA`) VALUES(?,?,?,?,?)',array($key,time(),$uid,$username,$_SERVER['HTTP_USER_AGENT']));
				$db->u_do_SQL('UPDATE `users` SET `last_login_time`=?,`last_login_ip`=? WHERE `uid`=?',array(time(),$_SERVER['REMOTE_ADDR'],$uid));
			}
		}
		public function try_to($type,$user,$password,$remember=false){
			if(!isset($_SESSION['black_list']))$_SESSION['black_list']=0;//session黑名单
			if(@$_SESSION['black_list']>5){
				return array('server_version'=>VERSION,'isok'=>0,'info'=>'抱歉，由于您频繁尝试登陆，本次会话已被系统拉入黑名单！');
			}
			$db=$this->system->db();
			$res=$db->u_do_SQL('SELECT COUNT(*) as t FROM `'.self::log_table.'` WHERE `ip`=? AND `time`>? AND `is_true`=0',array($_SERVER['REMOTE_ADDR'],time()-5*60));
			$t=@$res[0]['t'];
			if($t<5){//还未超过特定时间内的登陆上限（含本次）
				if($type=='name') $res=$db->u_do_SQL('SELECT `uid`,`password`,`username`,`s` FROM `users` WHERE `username`=?',array($user));
				elseif($type=='uid') $res=$db->u_do_SQL('SELECT `uid`,`password`,`username`,`s` FROM `users` WHERE `uid`=?',array($user));
				else return array('isok'=>0,'info'=>'未知或不被支持的登陆方式');
				if(!$res){
					$res=array(array());
					$res[0]['uid']=($type=='uid')?$user:null;
					$res[0]['username']=($type=='name')?$user:'';
					$is_ok=0;
				}else{
					//比对密码
					$is_ok=sha1(sha1($password).$res[0]['s'])==$res[0]['password']?1:0;
					/*记住登陆功能*/
					if($is_ok&&$remember){
						$remember_key=$this->system->rand(20);
						$db->do_SQL('INSERT INTO `'.self::re_table.'`(`key`,`uid`,`end_time`) VALUE(\''.$remember_key.'\','.$res[0]['uid'].','.($time=(time()+3600*24*7)).')');
						$rid=$db->do_SQL('SELECT max(`id`) AS `id` FROM `login_remember`');
						$rid=$rid[0]['id'];
						setcookie('r_login_key',$remember_key,$time,URLROOT);
						setcookie('r_login_id',$rid,$time,URLROOT);
					}
				}
				$this->add_login($res[0]['uid'],$res[0]['username'],$is_ok);
				return array('isok'=>$is_ok,'info'=>$is_ok?'':'抱歉，您的用户名密码有误，还有'.(5-$t-1).'次机会');
			}else{//禁止登陆
				isset($_SESSION['black_list'])?$_SESSION['black_list']++:$_SESSION['black_list']=1;
				return array('server_version'=>VERSION,'isok'=>0,'info'=>'已达登陆次数上限，请五分钟后再试');
			}
		}
		public function try_relogin(){
			if(isset($_COOKIE['r_login_key'])&&isset($_COOKIE['r_login_id'])){
				$_COOKIE['r_login_id']+=0;
				if($res=$this->system->db()->do_SQL('SELECT `uid`,`key` FROM `'.self::re_table.'` WHERE `id`='.$_COOKIE['r_login_id'])){
					if($res[0]['key']==$_COOKIE['r_login_key']){
						$this->add_login($res[0]['uid']);
						$new_key=$this->system->rand(20);
						$this->system->db()->do_SQL('UPDATE `'.self::re_table.'` SET `key`="'.$new_key.'" WHERE `id`='.$_COOKIE['r_login_id'].'`end_time`='.($time=(time()+3600*24*7)));
						setcookie('r_login_key',$new_key,$time,URLROOT);
						return $res[0]['uid'];
					}
				}
			}
			return false;
		}
		public function is_login(){
			if(isset($_COOKIE['login_key'])){
				$res=$this->system->db()->u_do_SQL('SELECT `uid`,`UA` FROM `'.self::table.'` WHERE `key`=? and `UA`=?',array($_COOKIE['login_key'],$_SERVER['HTTP_USER_AGENT']));
				if($res&&$_SERVER['HTTP_USER_AGENT']==$res[0]['UA']){
					return $res[0]['uid'];
				}
			}
			//var_dump($_COOKIE['login_key']);
			$_SESSION['userinfo']='';
			return $this->try_relogin();
		}
		public function logout(){
			setcookie('r_login_key',0,1,URLROOT);
			setcookie('r_login_id',0,1,URLROOT);
			$this->system->db()->u_do_SQL('delete from `'.self::table.'` where `key`=?',array($_COOKIE['login_key']));
			setcookie('login_key','',time()-10,URLROOT);
			$_SESSION['userinfo']='';
		}
		public function get_logins($uid=null){
			return $this->system->db()->do_SQL('SELECT `id`,`uid`,`username`,`UA` FROM `'.self::table.'`'.($uid===null?'':' WHERE uid='.$uid));
		}
		public function get_now_user(){
			if(($uid=$this->is_login())!==false){
				$res=$this->system->db()->do_SQL('SELECT `username` FROM `users` WHERE `uid`='.$uid);
				//var_dump($res);exit;
				$res[0]['key']=$_COOKIE['login_key'];
				return $res[0];
			}else{
				return array();
			}
		}
		const admin_cookie='adminkey';
		const admin_login_table='@%_admin_login';
		public function admin_login(){
			$this->system->db()->do_SQL('DELETE FROM `'.self::admin_login_table.'` WHERE `last_time`<'.(time()-300));
			if(isset($_COOKIE[self::admin_cookie])&&$this->system->db()->u_do_SQL('SELECT `uid` FROM `'.self::admin_login_table.'` WHERE `admin_key`=?',array($_COOKIE[self::admin_cookie]))){
				return $_COOKIE[self::admin_cookie];
			}
			if($u=$this->is_login()){
				$char=$this->system->rand(10);
				$this->system->db()->do_SQL('INSERT INTO `'.self::admin_login_table.'`(`admin_key`,`IP`,`uid`,`last_time`) VALUE(\''.$char.'\',\''.$_SERVER['REMOTE_ADDR'].'\','.$u.','.time().')');
				setcookie(self::admin_cookie,$char,time()+300,URLROOT);
				return $char;
			}
			return false;
		}
		public function clean_outher_login(){
			if(($uid=$this->is_login())===false){
				return;
			}
			$this->system->db()->u_do_SQL('DELETE FROM `'.self::table.'` WHERE `uid`=? and `key`!=?',array($uid,$_COOKIE['login_key']));
		}
		public function delete($id){
			$id+=0;
			$this->system->db()->do_SQL('DELETE FROM `'.self::table.'` WHERE `id`='.$id);
		}
	}