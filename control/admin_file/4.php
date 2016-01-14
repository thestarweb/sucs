<?php
$s=new app_server($system);
switch(isset($_GET['doing'])?$_GET['doing']:''){
	case 'add':
		$s->add($_POST['add_name'],$_POST['add_url']);
	default:
		$list=$s->get_list();
		include $system->get_view('admin/apps');
}