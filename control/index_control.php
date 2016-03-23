<?php
	class index_control{
		public function index_page($system){
			$login=new login_server($system);
			if($login->is_login()!==false){
				header('location: '.URLROOT.'home');
			}else{
				header('location: '.URLROOT.'index/login');
			}
		}
		public function myScript_page($system){
			header("Content-type:text/javascript");
			//require_once 
			//header('location: '.$system->ini_get('my_script_path'));
			echo 'alert("接口过旧，请联系管理员更新页面")';
		}
		public function login_page($system){
			$login=new login_server($system);
			if($login->is_login()!==false){
				header('location: '.URLROOT.'home');
			}else{
				$system->show_head('用户登录');
				require_once $system->get_view('login');
				$system->show_foot();
			}
		}
		public function reg_page($system){
			$system->show_head('注册页面');
			include $system->get_view('reg');
			$system->show_foot();
		}
		public function reg_for_key_page($system){
			$sname=$system->ini_get('reg_ver_ses_name');
			if(!isset($_GET['stp']))$_GET['stp']='';
			switch ($_GET['stp']) {
				case '1':
					if(!isset($_SESSION[$sname])||$_POST[$system->ini_get('reg_ver_ses_name')]!=$_SESSION[$system->ini_get('reg_ver_ses_name')]){
						$_SESSION[$sname]='';
						echo '验证码错误';
						exit;
					}
					$user=new user_server($system);
					$uids=$user->get_uids_by_reg_key($_POST['key']);
					$_SESSION[$sname.'_1']=$_SESSION[$sname];
					$_SESSION[$name]='';
					$system->show_json(array('uids'=>$uids));
					break;
				case '2':
					if(!isset($_SESSION[$sname])||$_SESSION[$sname.'_1']!=$_POST[$sname]) return;
					$user=new user_server($system);
					$res=$user->add_user_for_key($_POST['username'],$_POST['password'],$_POST['uid'],$_POST['key']);
					$system->show_json(array('info'=>$res));
					break;
				default:
					$system->show_head('邀请注册');
					require_once $system->get_view('reg_key');
					break;
			}
		}
		public function mail_error_page($system){
			if(!file_exists('./error.log')) return;
			$fp=fopen('./error.log','r');
			$errors=array();
			while($line=fgets($fp)){
				if($line=="\r\n") continue;
				array_push($errors,unserialize($line));
			}
			$mail=$system->mail();
			$mail->add_geter('597577939@qq.com','星星');
			$mail->send_star('故障报告发送测试');
			require_once $system->get_view('mail/error');
			require_once $system->get_view('mail/foot');
			if($mail->send_end()){
				file_put_contents('./error.log','');
				echo 'ok';
			}else{
				echo 'error';
			}
		}
	}
?>