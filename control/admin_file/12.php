<?php
namespace sucs; 
$doing=isset($_GET['doing'])?$_GET['doing']:'';
$u_s=new user_server($system);
switch ($doing) {
	case 'add':
		include $system->get_view('admin/reg_key_add');
		break;
	case 'delect':
		//include $system->get_view('admin/reg_key_add');
		break;
	
	default:
		$list=$u_s->get_reg_key_list();
		//var_dump($list);exit;
		include $system->get_view('admin/reg_key_list');
		break;
}

?>