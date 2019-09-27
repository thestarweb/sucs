<?php
namespace sucs; 
class install_control{
	public function index_page($system){
		echo '自动导入数据库等功能暂时还无法正常使用（过一段时间有空也会努力开发此功能），请第一次安装请先手动导入SQL语句后执行<a href="'.URLROOT.'install/update">更新数据库</a>，以后数据库更新都会更新XML文件，通过运行该页面可以完成自动更新数据库的功能';
	}
	public function update_page($system){
		if(file_exists(__DIR__.'/../update.lock')){
			echo '更新功能已被锁定，如需更新数据库，请删除update.lock';
			exit;
		}
		if(isset($_GET['yes'])){
			set_time_limit(0);
			$t=\dbhelper_tool::update($system->db(),__DIR__.'/../db.xml');
			if($t['succ']) echo '数据库更新已完成<br/>';
			else echo '数据库更新有错误<br/>';
			echo '<pre>'.$t['log'];
			fclose(fopen(__DIR__.'/../update.lock','w'));
			exit;
		}
		echo '这里是自动更新数据库的地方，如果需要更新请<a href="?yes=1">点我</a><br/>';
		echo '注意：1、此脚本还处在测试状态，为了避免应不可预期的BUG造成不必要的损失，请在更新前备份数据库。<br/>';
		echo '　　　2、更新用时可能较长，请保持网络通畅，页面长时间加载是正常现象，请勿反复刷新。';
	}
}
