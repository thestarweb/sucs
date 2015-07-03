<?php
	$login=new login_server($system);
	if(isset($_POST['doing'])){
		switch ($_POST['doing']) {
			case 'offline':
					if(isset($_POST['id'])){
						$login->delete($_POST['id']);
					}else{
						echo '非法请求';
					}
				break;
			
			default:
					echo '未知操作';
				break;
		}
		exit;
	}
	$list=$login->get_logins();
	require $system->get_view('admin/who_online');