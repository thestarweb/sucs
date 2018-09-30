<?php
namespace sucs; 
	class login_server{
		private $system;
		private $submit_safe;
		private $uid_cache=-1;
		public $user;
		const table='@%_logins';
		const log_table='@%_login_log';
		const re_table='@%_login_remember';
		const file_table='@%_filelogin';
		const file_start='SUCS_LOGIN_FILE';
		const file_version_string='FTLE_VERSION=';
		const file_key_length=128;
		public function __construct($system){
			$this->system=$system;
			$this->submit_safe=new submit_safe_server($system);
			if($this->submit_safe->is_locked(40)){
				echo ':)',$system->lang('errors',100);
				exit;
			}
		}
		/**
			增加一次用户登陆记录（如果登陆成功则同时写入登陆表）
			@uid int 用户uid
			@userneme string 尝试用户名
			@is_ok int 是否登陆成功
			return void
		*/
		public function add_login($uid,$username=0,$is_ok=1){
			$uid+=0;
			$db=$this->system->db();
			if($username===0){
				$res=$db->exec('SELECT `username` from `'.user_server::table.'` WHERE `uid`='.$uid);
				$username=$res[0]['username'];
			}
			//写入登陆日志
			$db->u_exec('INSERT INTO `'.self::log_table.'`(`time`, `ip`, `uid`, `username`, `HTTP_USER_AGENT`, `is_true`) VALUES(?,?,?,?,?,?)',
				array(time(),$_SERVER['REMOTE_ADDR'],$uid,$username,$_SERVER['HTTP_USER_AGENT'],$is_ok));
			//完成登陆
			if($is_ok){
				//
				$key=$this->system->rand(10);
				setcookie('login_key',$key,0,URLROOT);
				$_COOKIE['login_key']=$key;
				$db->u_exec('INSERT INTO `'.self::table.'`(`key`,`time`,`uid`,`username`,`UA`) VALUES(?,?,?,?,?)',array($key,time(),$uid,$username,$_SERVER['HTTP_USER_AGENT']));
				//登陆积分奖励
				$llt=$db->exec('SELECT `last_login_time` FROM `'.user_server::table.'` WHERE `uid`='.$uid);
				if(date('Y-m-d',$llt[0]['last_login_time'])!=date('Y-m-d')){
					$exc=new exc_server($this->system);
					$exc->add_for_login($uid);
				}
				//修改最后登录时间 需要在加积分之后
				$db->u_exec('UPDATE `'.user_server::table.'` SET `last_login_time`=?,`last_login_ip`=? WHERE `uid`=?',array(time(),$_SERVER['REMOTE_ADDR'],$uid));
			}
		}
		/**
			尝试登陆
			@type string 登陆方式（name=用户名，uid=用户id)
			@user mix 所选登陆方式的识别标识
			@password string 密码
			@remember bool 是否记住登陆
			@oLogin string 外部登陆时使用
		*/
		public function try_to($type,$user,$password,$remember=false){
			if(!isset($_SESSION['black_list']))$_SESSION['black_list']=0;//session黑名单
			if(@$_SESSION['black_list']>5){
				return array('server_version'=>VERSION,'isok'=>0,'info'=>'抱歉，由于您频繁尝试登陆，本次会话已被系统拉入黑名单！');
			}
			$system=$this->system;
			$max_time=$system->ini_get('max_try_login_time');
			$max_time_in=$system->ini_get('max_try_login_time_in');
			$db=$system->db();
			$res=$db->u_exec('SELECT COUNT(*) as t FROM `'.self::log_table.'` WHERE `ip`=? AND `time`>? AND `is_true`=0',array($_SERVER['REMOTE_ADDR'],time()-$max_time_in*60));
			$t=@$res[0]['t'];
			if($t<$max_time){//还未超过特定时间内的登陆上限（含本次）
				if($type=='name') $res=$db->u_exec('SELECT `uid`,`password`,`username`,`s` FROM `users` WHERE `username`=?',array($user));
				elseif($type=='uid') $res=$db->u_exec('SELECT `uid`,`password`,`username`,`s` FROM `users` WHERE `uid`=?',array($user));
				else return array('isok'=>0,'info'=>$system->lang('errors',5));
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
						$remember_key=$system->rand(20);
						$db->exec('INSERT INTO `'.self::re_table.'`(`key`,`uid`,`end_time`) VALUE(\''.$remember_key.'\','.$res[0]['uid'].','.($time=(time()+3600*24*7)).')');
						$rid=$db->exec('SELECT max(`id`) AS `id` FROM `login_remember`');
						$rid=$rid[0]['id'];
						setcookie('r_login_key',$remember_key,$time,URLROOT);
						setcookie('r_login_id',$rid,$time,URLROOT);
					}
				}
				$this->add_login($res[0]['uid'],$res[0]['username'],$is_ok);
				return array('isok'=>$is_ok,'info'=>$is_ok?'':$system->lang('errors',3,[$max_time_in-$t-1]));
			}else{//禁止登陆
				isset($_SESSION['black_list'])?$_SESSION['black_list']++:$_SESSION['black_list']=1;
				return array('server_version'=>VERSION,'isok'=>0,'info'=>$system->lang('errors',4,[$max_time_in,$max_time]));
			}
		}
		/**
			尝试通过记住登陆的信息登陆
			return mix uid(int)登陆成功的uid false(bool)登陆失败
		*/
		public function try_relogin(){
			if(isset($_COOKIE['r_login_key'])&&isset($_COOKIE['r_login_id'])){
				$_COOKIE['r_login_id']+=0;
				if($res=$this->system->db()->exec('SELECT `uid`,`key` FROM `'.self::re_table.'` WHERE `id`='.$_COOKIE['r_login_id'])){
					if($res[0]['key']==$_COOKIE['r_login_key']){
						$this->add_login($res[0]['uid']);
						$new_key=$this->system->rand(20);
						$this->system->db()->exec('UPDATE `'.self::re_table.'` SET `key`="'.$new_key.'" WHERE `id`='.$_COOKIE['r_login_id'].'`end_time`='.($time=(time()+3600*24*7)));
						setcookie('r_login_key',$new_key,$time,URLROOT);
						return $res[0]['uid'];
					}
				}
				setcookie('r_login_key','',0,URLROOT);
				setcookie('r_login_id','',0,URLROOT);
				$this->submit_safe->add(8);
			}
			return false;
		}
		/**
			文件登陆专用函数（由于和以前的登陆方式完全不同所以单独设立函数）
			@file string 登陆时上传的文件路径
			return int 匹配失败的位置（匹配成功则登陆并返回0）
		*/
		public function file_login($file){
			//if(isset($_FILES['FILE'])&&$_FILES['FILE']['error']) $system->show_json(array('error'=>'未能得到文件'));
			$fp=fopen($file,'r');
			if(fgets($fp)!=self::file_start."\r\n") return -2;

			$v=fgets($fp);
			if(strpos($v,self::file_version_string)!==0)return 2;
			$v=substr($v,strlen(self::file_version_string));

			$fid=substr(fgets($fp),0,-2)+0;

			if($v=="2.1\r\n") fgets($fp);//s
			else if($v!="2.0\r\n") return 2;

			$u_key=fgets($fp);
			if(strlen($u_key)!=self::file_key_length) return 3;
			$info=$this->system->db()->exec('SELECT `key`,`file_md5`,`end_time`,`uid` FROM `@%_filelogin` WHERE `logid`='.$fid);
			$res=$info&&time()<$info[0]['end_time']&&$u_key==$info[0]['key']&&md5_file($file)==$info[0]['file_md5'];
			fclose($fp);
			$res&&$this->add_login($info[0]['uid']);
			return $res?0:4;
		}
		/**
			本函数用于删除登陆文件
			@fid int
			@f bool 是否强制删除（非强制删除时只能删除当前账号下的数据）
			return void
		*/
		public function del_loginfile($fid,$f=false){
			$fid+=0;
			if(!$f){
				$info=$this->system->db()->exec('SELECT `uid`,`add_time` FROM `@%_filelogin` WHERE `logid`='.$fid);
				$uid=$this->is_login();
				if($info[0]['uid']!=$uid){
					if($info[0]['add_time']!=0||$fid!=$uid) return;
				}
			}
			$this->system->db()->exec('DELETE FROM `@%_filelogin` WHERE `logid`='.$fid);
		}
		/**
			本函数用于创建登陆文件
			@uid int 需要创建登陆文件的用户uid
			@end_time int 失效时间
			return void
		*/
		public function create_loginfile($uid,$end_time){
			$uid+=0;
			$key=$this->system->rand(self::file_key_length);
			$this->system->db()->exec('INSERT INTO `@%_filelogin`(`uid`,`key`,`file_md5`,`version`,`add_time`,`end_time`) VALUE('.$uid.',\''.$key.'\',0,\'2.1\','.time().','.$end_time.')');
			$fid=$this->system->db()->exec('SELECT max(`logid`) AS `fid` FROM `@%_filelogin`');
			header('Content-type:  application/octet-stream');
			header('Content-Disposition:  attachment;  filename='.$uid.'.slogin');
			$string=self::file_start."\r\n";
			$string.=self::file_version_string.'2.1'."\r\n";
			$string.=$fid[0]['fid']."\r\n";
			$string.=md5(time()+rand(0,99999)).$this->system->rand(50)."\r\n";
			$string.=$key;
			$md5=md5($string);
			$this->system->db()->exec('UPDATE `@%_filelogin` SET `file_md5`=\''.$md5.'\'');
			echo $string;
		}
		/**
			用于检测用户是否登陆（会尝试检测记住登陆信息）
			return uid(int)登陆成功的uid false(bool)登陆失败
		*/
		public function is_login(){
			if($this->uid_cache!=-1) return $this->uid_cache;
			if(isset($_COOKIE['login_key'])){
				$res=$this->system->db()->u_exec('SELECT `uid`,`UA` FROM `'.self::table.'` WHERE `key`=?',array($_COOKIE['login_key']));
				if($res&&$_SERVER['HTTP_USER_AGENT']==$res[0]['UA']){
					//匹配成功
					if(rand(0,20)==0){
						//有概率刷新key
						$key=$this->system->rand(10);
						setcookie('login_key',$key,0,URLROOT);
						$this->system->db()->u_exec('UPDATE `'.self::table.'` SET `key`=? WHERE `key`=?',array($key,$_COOKIE['login_key']));
					}
					return $this->uid_cache=$res[0]['uid'];
				}
				setcookie('login_key','',0,URLROOT);
				$this->submit_safe->add(5);
			}
			//var_dump($_COOKIE['login_key']);
			unset($_SESSION['userinfo']);
			return $this->uid_cache=$this->try_relogin();
		}
		/**
			退出当前用户
			return void
		*/
		public function logout(){
			setcookie('r_login_key',0,1,URLROOT);
			setcookie('r_login_id',0,1,URLROOT);
			$this->system->db()->u_exec('delete from `'.self::table.'` where `key`=?',array($_COOKIE['login_key']));
			setcookie('login_key','',time()-10,URLROOT);
			unset($_SESSION['userinfo']);
		}
		/**
			获取登录信息
			return array 包含信息的二维数组
		*/
		public function get_logins($uid=null){
			return $this->system->db()->exec('SELECT `id`,`key`,`uid`,`username`,`UA` FROM `'.self::table.'`'.($uid===null?'':' WHERE uid='.$uid));
		}
		/**
			获取当前登录用户的key
			return string 当前登录用户的key（若用户未登陆着返回空字符串）
		*/
		public function get_now_key(){
			return isset($_COOKIE['login_key'])?$_COOKIE['login_key']:'';
		}
		/**
				获取用户的用于登录的文件信息
				@uid int 指定用户id（可选）
				return array 登录文件信息的二维数组
		*/
		public function get_loginfile_info($uid=null){
			$uid+=0;
			return $this->system->db()->exec('SELECT `logid`,`add_time`,`end_time` FROM `'.self::file_table.'`'.($uid===null?'':' WHERE `uid`='.$uid.' or `add_time`=0&&`logid`='.$uid));
		}
		/**
			用于读取当前用户的信息（session缓存）
			@flash bool 是否强制刷新session中的缓存
			return array 用户信息数组
		*/
		public function get_now_user($flash=false){
			if(($uid=$this->is_login())!==false){
				//$res=$this->system->db()->exec('SELECT `username` FROM `users` WHERE `uid`='.$uid);
				//var_dump($res);exit;
				if(!isset($_SESSION['userinfo'])||$flash||$_SESSION['userinfo']['key']!=$_COOKIE['login_key']){
					if(!$this->user) $this->user=new user_server($this->system);
					$_SESSION['userinfo']=$this->user->get_user_info($uid);
					$_SESSION['userinfo']['key']=$_COOKIE['login_key'];
				}
				return $_SESSION['userinfo'];
			}else{
				return array();
			}
		}
		//后台登陆隔离，这部分代码需要重新考虑
		const admin_cookie='adminkey';
		const admin_login_table='@%_admin_login';
		public function admin_login(){
			$this->system->db()->exec('DELETE FROM `'.self::admin_login_table.'` WHERE `last_time`<'.(time()-300));
			if(isset($_COOKIE[self::admin_cookie])&&$this->system->db()->u_exec('SELECT `uid` FROM `'.self::admin_login_table.'` WHERE `admin_key`=?',array($_COOKIE[self::admin_cookie]))){
				return $_COOKIE[self::admin_cookie];
			}
			if($u=$this->is_login()){
				$char=$this->system->rand(10);
				$this->system->db()->exec('INSERT INTO `'.self::admin_login_table.'`(`admin_key`,`IP`,`uid`,`last_time`) VALUE(\''.$char.'\',\''.$_SERVER['REMOTE_ADDR'].'\','.$u.','.time().')');
				setcookie(self::admin_cookie,$char,time()+300,URLROOT);
				return $char;
			}
			return false;
		}
		//重新考虑后再做决定的代码结束
		/**
			清除除本会话外的所有登陆
		*/
		public function clean_outher_login(){
			if(($uid=$this->is_login())===false){
				return;
			}
			$this->system->db()->u_exec('DELETE FROM `'.self::table.'` WHERE `uid`=? and `key`!=?',array($uid,$_COOKIE['login_key']));
		}
		/**
			查询登陆信息是否属于当前用户
			@id int 要查询的登陆id
			return bool 是否属于当前用户
		*/
		public function is_belong_to($id){
			$arr=$this->get_logins();
			foreach($arr as $v){
				if($v['id']==$id) return true;
			}
			return false;
		}
		/**
			强制退出指定的登陆
			@id int 登陆的id
			return void
		*/
		public function delete($id){
			$id+=0;
			$this->system->db()->exec('DELETE FROM `'.self::table.'` WHERE `id`='.$id);
		}
	}