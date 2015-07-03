<?php
$user=new user_server($system);
$users_list=$user->get_users_list();
require $system->get_view('admin/user_list');