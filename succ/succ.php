<?php
class succ{
	//cons table='succ`.`users`';
	public static $self_obj=null;
	public static function get_obj(){
		if(!self::$self_obj) self::$self_obj=new succ();
		return self::$self_obj;
	}
	public $cooke_pass='/';
	public $cooke_name='succ_key';
	public $link_use;
	private $js_key;
	public function get_js_key(){
		return $this->js_key;
	}
	public function __construct(){
		require_once __DIR__.'/usedb.php';$this->link_use=new db_link();
		$this->js_key=rand(100,999);
	}
	public function login_page(){
		return $this->link_use->server_url.'/index/login';
	}
	public function show_script(){
		echo '<script>
		(function(){
			var suc_u_info;
			var load_array;
			window.suc_u_load=function(fun,key){
				if(key!='.$this->js_key.') return;
				if(suc_u_info){
					fun(suc_u_info);return;
				}
				if(!load_array){
					load_array=[fun];
				}else{
					load_array[load_array.arr.length]=fun;
				}
			}
			var ajax=$.ajax();
			ajax.ajax.withCredentials = true
			ajax.page="'.$this->link_use->server_url.'/api";
			//alert(ajax.page);
			ajax.data="app=1";
			ajax.callback=function(ajax){
				if(ajax.json){
					suc_u_info=ajax.json;
					for(var i in load_array){
						load_array[i](suc_u_info);
					}
				}else{
					alert("sucs error：\n"+ajax.text);
				}
			}
			ajax.send();
		})();
		</script>';
	}
	public function is_login($key,$UA){
		return $this->link_use->islogin($key,$UA);
	}
	public function set_key($key){
		setcookie($this->cooke_name,$key);
	}
}