<?php
	class p_control{
		public function login_page($system){
			//echo 'SELECT COUNT(*) FROM `logins` WHERE `ip`=\''.$_SERVER['REMOTE_ADDR'].'\' AND `time`>'.(time()-5*60);
			//var_dump(isset($_POST['remember'])&&$_POST['remember']);exit;
			if(!isset($_SESSION['login'])||$_SESSION['login']===''||!isset($_POST['login'])||$_POST['login']!=$_SESSION['login']){
				$system->show_json(array('isok'=>0,'info'=>'验证码有误'));
				exit;
			}
			$_SESSION['login']='';
			$login=new login_server($system);
			//var_dump($_POST);
			//exit;
			$system->show_json($login->try_to('name',$_POST['username'],$_POST['password'],isset($_POST['remember'])&&$_POST['remember']));
		}
		public function send_message_page($system){
			$login=new login_server($system);
			$uid=$login->is_login();
			if($uid!==false&&null!=$_POST['body']&&null!=$_POST['geter']){
				$geters=explode("\r\n",$_POST['geter']);
				$geters=array_unique($geters);
				$errors=array();
				$ng=array();
				$get_uid=$system->db()->prepare('SELECT `uid` FROM `users` WHERE `username`=?');
				foreach ($geters as $v){
					if($v){
						$get_uid->execute(array($v));
						if($u=$get_uid->fetchAll()){
							$ng[]=$u[0]['uid'];
						}else{
							$errors[]=$v;
						}
					}
				}
				$m=new message_server($system);
				$m->send_messages($uid,$_POST['body'],$ng);
				$system->show_json(array('info'=>$errors));
			}
		}
		public function logout_page($system){
			$server=new login_server($system);
			$server->logout();
			header('location: '.URLROOT.'index/login');
		}
		//error说明:0表示注册成功，-1 验证码错误，1 用户名已存在
		public function reg_page($system){
			$sname=$system->ini_get('reg_ver_ses_name');
			//var_dump($_SESSION,$_POST);exit;
			if(!isset($_SESSION[$sname])||!$_SESSION[$sname]||$_POST['add']!=$_SESSION[$sname]){
				$_SESSION[$sname]='';
				$system->show_json(array('error'=>-1));
				exit;
			}
			$user=new user_server($system);
			$system->show_json(array('error'=>$user->add_user($_POST['username'],$_POST['password'])));
		}
		public function reg_for_key_page($system){
			$sname=$system->ini_get('reg_ver_ses_name');
			switch (@$_GET['stp']) {
				case '1':
						if(@!$_SESSION[$sname]||$_POST[$sname]!=$_SESSION[$sname]){
							$_SESSION[$sname]='';
							echo '验证码错误';
							exit;
						}
						$user=new user_server($system);
						if($res=$user->match_reg_key($_POST['key'])){
							$_SESSION[$sname.'_1']=$_SESSION[$sname];
							$_SESSION['reg_key']=$_POST['key'];
						}else{
							$res=array('error'=>1);
						}
						$_SESSION[$sname]='';
						$system->show_json($res);
					break;
				case '2':
						if(@!$_SESSION[$sname.'_1']||$_POST[$sname]!=$_SESSION[$sname.'_1']){
							$_SESSION[$sname]='';
							echo '验证码错误';
							exit;
						}
						$user=new user_server($system);
						$error=$user->reg_key($_SESSION['reg_key'],$_POST['uid'],$_POST['username'],$_POST['password'],$_POST['group']=='auto'?'':$_POST['group']);
						$system->show_json(array('error'=>$error));
					break;
			}
		}
		public function delete_message_page($system){
			if(!isset($_POST['mid'])) $system->show_json(array('info'=>'无法取得消息id'));
			$login=new login_server($system);
			if(($uid=$login->is_login())===false) $system->show_json(array('info' =>'需要登录才有权限'));
			$_POST['mid']+=0;
			$m=new message_server($system);
			$r=$m->get_message($_POST['mid']);
			if($r&&$t=$m->is_true_message($r,$uid)) {
				$m->delete($_POST['mid'],$t);
				$system->show_json(array('info'=>''));
			}
			$system->show_json(array('info'=>'消息不存在或别人的消息'));
		}
		public function chage_password_page($system){
			if(!isset($_POST['old_password'])||!isset($_POST['new_password'])) $system->show_json(array('info'=>'缺少必要参数'));
			$login=new login_server($system);
			if(($uid=$login->is_login())===false) $system->show_json(array('info' =>'没有发现登陆信息'));
			$res=$login->try_to('uid',$uid,$_POST['old_password']);
			if($res['isok']){
				$user=new user_server($system);
				$user->change_password($uid,$_POST['new_password']);
				$login->clean_outher_login($uid);
				$system->show_json(array('info'=>''));
			}else{
				$system->show_json(array('info' =>'原密码错误'));
			}
		}
		public function save_head_page($system){
			$login=new login_server($system);
			$uid=$login->is_login();
			if($uid===false) $system->show_json(array('info'=>'登录信息丢失'));
			list(,$imgData)=explode(',',$_POST['img'],2);
			if(isset($imgData)){
				file_put_contents($system->ini_get('imgs_dir').'u_head/'.$uid.'.jpg',base64_decode($imgData));
				$system->show_json(array('info'=>''));
			}
			$system->show_json(array('info'=>'未发现图片'));
		}
	}