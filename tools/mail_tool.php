<?php
class mail_tool{
	private $is_ok=false;
	public function __construct($name,$server,$username,$password,$charset='utf-8'){
		require_once dirname(__FILE__).'/PHPMailer/class.phpmailer.php';
		//include dirname(__FILE__).'/PHPMailer/class.'.$type.'.php';
		$this->mail=new PHPMailer();
		$this->mail->CharSet =$charset;
		$this->host=$server;
		$this->username=$username;
		$this->password=$password;
		$this->name=$name;
	}
	public function smtp(){
		$this->mail->IsSMTP();
		//$this->mail->SMTPDebug  = 1;//
		$this->mail->Host=$this->host;
		//$this->mail->Port=465;
		$this->mail->Username=$this->username;
		$this->mail->Password=$this->password;
		$this->mail->SMTPAuth=true;
		$this->mail->SetFrom($this->username,$this->name);
		$this->mail->SMTPAuth= true;
		$this->is_ok=true;
	}
	public function add_geter($mail,$name=''){
		$this->mail->AddAddress($mail,$name);
	}
	public function send($Subject,$body){
		if($this->is_ok){
			$this->mail->Subject=$Subject;
			$this->mail->MsgHTML($body);
			if(!$this->mail->Send()) {
				$this->error=$this->mail->ErrorInfo;
				return false;
			} else {
			 	return true;
			}
		}
	}
	private $ob_temp='';
	private $title='';
	private $start=false;
	public function send_star($title=''){
		if($this->is_ok){
			$this->ob_temp=ob_get_contents();
			ob_clean();
			$this->title=$title;
			$this->start=true;
		}
	}
	public function send_end(){
		if($this->start){
			$type=$this->send($this->title,ob_get_contents());
			ob_clean();
			echo $this->ob_temp;
			$this->start=false;
			return $type;
		}else{
			trigger_error('mail_tool error:在使用send_start前使用了send_end',512);
		}
	}
}