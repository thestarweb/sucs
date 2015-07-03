<?php
	class pdo_mysql{
		public $system;
		public $pdo=null;
		public function __construct($host,$username,$password,$db,$charset='utf8'){
			try {
				$this->pdo=new PDO('mysql:host='.$host.';dbname='.$db,$username,$password);
				$this->pdo->query('set names '.$charset);
			} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
			}
		}
		//执行sql
		public function do_SQL($sql){
			list($doing)=explode(' ',$sql);
			$doing=strtoupper($doing);
			if($doing=='SELECT'||$doing=='SHOW'){
				$res=$this->pdo->query($sql);
				$error=$this->pdo->errorInfo();
				if($error[1]){
					$this->system->show_error($this->pdo->errorInfo());
				}
				if(is_object($res)){
					return $res->fetchAll();
				}else{
					if($res===false){
						var_dump($this->pdo->errorInfo());
					}
					return $res;
				}
			}else{

				$res=$this->pdo->exec($sql);
				$error=$this->pdo->errorInfo();
				if($error[1]){
					$this->system->show_error($this->pdo->errorInfo());
				}
				return $res;
			}
		}
		public function prepare($sql){
			return $this->pdo->prepare($sql);
		}
		public function u_do_SQL($sql,$arr,$fetch_type=PDO::FETCH_ASSOC,$fetch_style=''){
			$sth=$this->prepare($sql);
			$sth->execute($arr);
			$error=$sth->errorInfo();
			if($error[1]){
				trigger_error('mysql_tool error:'.$error[1].'故障信息'.$error[2],512);
			}
			return($sth->fetchAll($fetch_type));
		}
		public function get_insert_id(){
			return $this->pdo->lastInsertId();
		}
	}
?>