<?php
	class system{
		private $is_phone;
		private static $self_obj;
		public static function get_system(){
			return self::$self_obj;
		}
		private $cfgs=array(//默认的配置
			'db_type'=>'mysql',
			'db_server'=>'127.0.0.1',
			'db_username'=>'root',
			'db_password'=>'root',
			'db_name'=>'sucs',
			'db_prefix'=>'',
			'tools_dir'=>'./tools',
			'servers_dir'=>'./servers',
			'controls_dir'=>'./control',
			'views_dir'=>'./view',
			'imgs_dir'=>'./img/',
			'imgs_url'=>'./img',
			'styles_url'=>'./style',
			'root'=>'use_server_dir',
			'my_script_path'=>'/myScript2.js',
			'use_key_reg_oney'=>0,
			'reg_ver_ses_name'=>'reg',
			'off_info'=>'',
			'debug'=>0
		);//用于存放配置文件
		public function __construct($ini='./cfg.ini',$sfc=''){
			if(self::$self_obj){
				
			}else{
				self::$self_obj=$this;
			}
			
			@define('URLROOT',$this->dir(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']))));
			$this->load_cfg($ini);//载入配置
			
			if(isset($_GET['phone'])){
				setcookie('phone',($this->is_phone=$_GET['phone']?1:0),0,URLROOT);
			}else{
				if(isset($_COOKIE['phone'])){
					$this->is_phone=$_COOKIE['phone'];
				}else{
					setcookie('phone',$this->is_phone=isset($_SERVER['HTTP_X_REQUESTED_WITH'])||stripos($_SERVER['HTTP_USER_AGENT'],'Mobile'),0,URLROOT);
				}
			}
			
			ob_start();
			session_start();
			header('charset: utf-8');
			header("Content-Type: text/html;charset=utf-8");
			date_default_timezone_set('PRC');
			set_error_handler(array($this,'for_error'));//注册故障处理函数

			spl_autoload_register(array($this,'load_class'),E_ALL);//类的自动加载

			if($this->cfgs['off_info']){
				echo $this->cfgs['off_info'];
				exit;
			}
			
			if($sfc){
				require_once $sfc;
			}
			
			//URL解析
			list($path)=explode('?',$_SERVER['REQUEST_URI']);
			$temp=strlen(URLROOT);
			$c=explode('/',substr($path,$temp),3);
			//var_dump(isset($c[1]));exit;
			$this->show($c[0],isset($c[1])?$c[1]:'index',isset($c[2])?$c[2]:'');
		}
		//自动加载类的方法
		public function load_class($classname){
			if(file_exists($this->cfgs['controls_dir'].$classname.'.php')) include_once $this->cfgs['controls_dir'].$classname.'.php';
			elseif(file_exists($this->cfgs['servers_dir'].$classname.'.php')) include_once $this->cfgs['servers_dir'].$classname.'.php';
			elseif(file_exists($this->cfgs['tools_dir'].$classname.'.php')) include_once $this->cfgs['tools_dir'].$classname.'.php';
		}
		//载入配置（ini文件或已处理过生成的temp文件）
		private function load_cfg($pass='./cfg.ini'){
			if(file_exists($pass)){
				$md5=md5_file($pass);
				if(file_exists($pass.'.temp')){
					$arr=unserialize(file_get_contents($pass.'.temp'));
					if($arr['md5']==$md5){//文件没有修改
						$this->cfgs=$arr['cfgs'];
						return;
					}
				}
				$this->read_ini($pass);
				file_put_contents($pass.'.temp',serialize(array('md5'=>$md5,'cfgs'=>$this->cfgs)));
			}else{
				if(file_exists($pass.'.temp')){
					$arr=unserialize(file_get_contents($pass.'.temp'));
					if($arr['md5']=='d'){//默认的配置的临时文件
						$this->cfgs=$arr['cfgs'];
						return;
					}
				}
				$this->rewrite_cfg();
				file_put_contents($pass.'.temp',serialize(array('md5'=>'d','cfgs'=>$this->cfgs)));
			}
		}
		//读取配置文件
		private function read_ini($pass){
			$fp=fopen($pass,'r');//打开文件
			$line=0;//行变量
			while($str=fgets($fp)){//读取文件
				$line++;//每读取一行，行变量自增1
				
				//过滤空格，制表符以及注释（仅支持单行用#注释）
				$cfg='';
				for($i=0;($char=substr($str,$i,1))!==false;$i++){
					if($char=='#'){
						break;
					}elseif($char=="\t"||$char==' '||$char=="\n"||$char=="\r"){
						continue;
					}
					$cfg.=$char;
				}
				//检查是否有实际内容
				if($cfg){
					@list($k,$v)=explode("=",$cfg,2);
					//是否是有效地配置
					if(array_key_exists($k,$this->cfgs)){
						$this->cfgs[$k]=$v;
					}elseif(stripos($k,'p_')===0){
						$this->cfgs[$k]=$v;
					}else{
						throw new cfg_error($k,$line,6);
					}
				}
			}
			fclose($fp);
			$this->rewrite_cfg();
		}
		
		//进一步解析
		private function rewrite_cfg(){
			if($this->cfgs['root']=='use_server_dir'){
				$this->cfgs['root']=dirname(__FILE__);
			}elseif($this->cfgs['root']=='use_index_dir'){
				die('暂不支持用index文件做根目录');
			}
			$this->cfgs['imgs_url']=$this->get_full_URL($this->cfgs['imgs_url']);
			$this->cfgs['styles_url']=$this->get_full_URL($this->cfgs['styles_url']);
			$this->dir($this->cfgs['views_dir']);
			$this->dir($this->cfgs['tools_dir']);
			$this->dir($this->cfgs['servers_dir']);
			$this->dir($this->cfgs['controls_dir']);
			$this->dir($this->cfgs['img_dir']);	
			//var_dump($this->cfgs);//exit;
			//die($this->cfgs['img_url']);
		}
		
		//获取完整路径
		/*public function get_full_path($path){
			if(PHP_OS=='WINNT'){
				if(substr($path,1,2)!=':/'){
					//拼接
					$path=$this->cfgs['root'].$path;
				}
			}
			if(substr($path,-1,1)!='/'){
				$path.='/';
			}
			return $path;
		}*/
		public function dir(&$path){
			if(substr($path,-1,1)!='/'){
				$path.='/';
			}
			return $path;
		}
		//获取完整URL
		public function get_full_URL($path){
			if(substr($path,0,1)!='/'){
				//拼接
				$path=URLROOT.$path;
			}
			if(substr($path,-1,1)!='/'){
				$path.='/';
			}
			return $path;
		}
		
		//展示页面
		public function show($server='index',$function='index',$c){
			$obj_name=($server?$server:'index').'_control';
			if(class_exists($obj_name)){
				$obj=new $obj_name($this);
				$function_name=($function!==''?$function:'index').'_page';
				if(is_callable(array($obj,$function_name))){
					call_user_func(array($obj,$function_name),$this,$c);
					return;
				}
			}
			header('HTTP/1.1 404 Not Found');
			header("status: 404 Not Found");
			//require_once $this->get_view('error/404');
		}
		//展示头部（减少重复的html头部）
		public function show_head($title,$css=array()){
			if(!isset($_POST['ajax'])){
				require_once $this->get_view('head');
			}else{
				$this->title=$title;
			}
		}
		//展示尾部（减少重复的html尾部）
		public function show_foot(){
			if(!isset($_POST['ajax'])){
				include $this->get_view('foot');
			}else{
				$this->show_json(array('title'=>$this->title?$this->title:'无标题','body'=>ob_get_contents()));
			}
		}
		//获取ini配置
		public function ini_get($name){
			
			return @$this->cfgs[$name];
		}
		public function get_view($name){
			//var_dump($this->is_phone,$name);
			if($this->is_phone){
				$file=$this->cfgs['views_dir'].$name.'_phone.html';
				return file_exists($file)?$file:($this->cfgs['views_dir'].'phone_nofile.html');
			}else{
				return $this->cfgs['views_dir'].$name.'.html';
			}
		}

		//
		private  $link;
		public function db(){
			if(!$this->link){
				$this->link=new pdo_mysql($this->cfgs['db_server'],$this->cfgs['db_username'],$this->cfgs['db_password'],$this->cfgs['db_name'],$this->cfgs['db_prefix']);
				$this->link->system=$this;
			}
			return $this->link;
		}
		private $_mail;
		public function mail(){
			if(!$this->_mail){
				$this->_mail=new mail_tool('星星站点开发部','smtp.163.com','thestarweb@163.com','16300lll');
				$this->_mail->smtp();
			}
			return $this->_mail;
		}
		public function show_json($arr){
			ob_clean();
			if(is_array($arr)){
				$arr['server_version']=VERSION;
				echo json_encode($arr);
			}
			exit;
		}
		public function show_error($message){
			ob_clean();
			print_r($message);
			exit;
		}
		public function rand($lens){
			$lens+=0;
			if($lens<1){
				return '';
			}
			$str='ABCDEGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
			for($time=$lens%10;$time>0;$time--) $str.=$str;
			return substr(str_shuffle($str),0,$lens);
		}
		//故障处理函数
		public function for_error($errno,$errstr,$errfile,$errline){
			if($this->cfgs['debug']){
				ob_clean();
				echo '错误'.$errno.':'.$errstr.'<br/>';
				echo '<table>';
				$array =debug_backtrace();
				//unset($array[0]);
				//var_dump($array);
				$call=null;
				foreach($array as $v){
					if(isset($v['file'])){
						echo '<tr><td>'.$v['file'].'</td><td>'.(isset($v['line'])?$v['line']:'').'</td><td>'.(isset($v['class'])?$v['class'].$v['type']:'').$v['function'].' '.$call.'</td></tr>';
						$call=null;
					}else{
						$call=(isset($v['class'])?$v['class'].$v['type']:'').$v['function'];
					}
					//isset($v['file'])||var_dump($v);
				}
				echo '</table>';
				exit;
			}else{
			$file=explode('\\',$errfile);
			$file=explode('/',array_pop($file));
			$fp=fopen('./error.log','a');
			fwrite($fp,"\r\n".serialize(array('time'=>date('Y-m-d h:m:s'),'file'=>array_pop($file),'line'=>$errline,'info'=>$errstr,'page'=>$_SERVER['REQUEST_URI']))."\r\n");
			fclose($fp);
			require $this->get_view('error/500');
		}
		}
	}
	
	//故障处理类
	class cfg_error extends Exception{
		// 重定义构造器使 message 变为必须被指定的属性
		public function __construct($k,$line, $code = 0) {
			// 自定义的代码
			$this->k=$k;
			$this->line=$line;
			// 确保所有变量都被正确赋值
			parent::__construct('警告：无法识别“'.$k.'”在配置文件第'.$line.'行', $code);
		}
	
		// 自定义字符串输出的样式
		public function __toString() {
			return __CLASS__ . '警告：无法识别“'.$this->k.'”在配置文件第<b>'.$this->line.'</b>行';
		}
	
		public function customFunction() {
			echo "A Custom function for this type of exception\n";
		}
	}
?>