<?php
	class home_control{
		public $system;
		public function __construct($system){
			$this->login=new login_server($system);
			if(($this->uid=$this->login->is_login())===false){
				header('location: '.URLROOT.'index/login');
				exit;
			}
			$system->show_head('星星站点用户中心');
			$this->user=new user_server($system);
			$this->system=$system;
			$exc=new exc_server($this->system);
			$exc_change=$exc->read_log($this->uid);
			$this->login->get_now_user($exc_change?true:false);
			require_once $system->get_view('home/head');
		}
		public function __destruct(){
			$this->system->show_foot();
		}
		public function index_page($system){
			$user=$_SESSION['userinfo'];
			//var_dump($user);
			$m=new message_server($system);
			$messages=$m->get_message_list($user['uid'],true,true);
			$login_his=$this->user->get_history_login($user['uid']);
			require_once $system->get_view('home/index');
		}
		public function send_message_page($system){
			require_once $system->get_view('home/send_message');
		}
		public function message_page($system){
            		$m=new message_server($system);
			if(isset($_GET['mid'])){
				$messages=$m->get_message($_GET['mid']);
				if($messages&&$m->is_true_message($messages,$_SESSION['userinfo']['uid'])){
					if($messages['geter']==$_SESSION['userinfo']['username']){
						if(($messages['type']&1)==0){
							$m->set_message_is_red($messages['mid']);
						}
					}
					require_once $system->get_view('home/message');return;
				}
				echo '你要查看的消息不存在或已被删除';
			}elseif(isset($_GET['send'])&&null!=$_POST['body']&&null!=$_POST['geter']){
				ob_clean();
				$m->send_message($_SESSION['userinfo']['uid'],$_POST['body'],$_POST['geter']);
				$system->show_json(array('is_ok'=>1));
				exit;
			}else{
				$mlist=$m->get_message_list($_SESSION['userinfo']['uid']);
				require_once $system->get_view('home/messages');
			}
		}

		public function  info_page($system){
			$user=$_SESSION['userinfo'];
			$exc_server=new exc_server($system);
			$exc=$exc_server->get_by_uid($user['uid']);
			include $system->get_view('home/my_info');
		}

		public function friends_page($system){
			echo '糟糕，这个功能还没有弄好';
		}

		public function soft_page($system){
			$login_his=$this->user->get_history_login($this->uid);
			$login_c=$this->login->get_logins($this->uid);
			include $system->get_view('home/soft');
		}
		public function change_haad_page($system){
			include $system->get_view('home/change_head');
		}

		public function market_page($system){
			$goods=$system->db()->do_SQL('SELECT * FROM `goods`');
			include $system->get_view('home/market');
		}
	}