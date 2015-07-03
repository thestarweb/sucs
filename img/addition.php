<?php
/*  100以内加减法验证码图片版v1.0.1.0002  */
/*     版权归星星站点所有，保留所有权利    */
/*                by 星星              */
/*严禁任何人对本代码进行任何*/
	header('Cache-Control: no-cache, must-revalidate');//禁用缓存
	session_start();//为后面将数据保存到session做准备
	$name=@$_GET['name']?$_GET['name']:'addition';
	$number1=rand(30,70);//生成第一个数字（伪随机数）
	$number2=rand(0,30);//生产第二个数字
	$operator=rand(0,1);//生成运算符，0表示+，1表示-
	$image=imagecreatetruecolor(50,20);//创建画布（背景为黑色）
	//通过for循环创建干扰机器人的线条，这里生成十条
	for($i=0;$i<10;$i++){
		//随机生成一个颜色（这里生成的颜色比较暗，以便于用户可以看清，而机器人无法看清
		$color[0]=imagecolorallocate($image,rand(0,200),rand(0,200),rand(0,200));
		//使用随机颜色在画布的随机位置绘制直线
		imageline($image,rand(0,50),rand(0,20),rand(0,50),rand(0,20),$color[0]);
	}
	//下面是用于给算式的随机颜色（较亮）
	$color[1]=imagecolorallocate($image,255,0,0);
	$color[2]=imagecolorallocate($image,0,255,0);
	$color[3]=imagecolorallocate($image,150,150,255);
	$color[4]=imagecolorallocate($image,200,200,200);
	//将第一个数绘制到画布
	imagestring($image,5,2,1,$number1,$color[rand(1,4)]);
	//判断运算符
	if($operator==0){
		//加法
		$_SESSION[$name]=$number1+$number2;//将结果保存至session
		imagestring($image,5,20,1,"+",$color[rand(1,4)]);//在画布上绘制运算符
	}else{
		//减法（详细同上）
		$_SESSION[$name]=$number1-$number2;
		imagestring($image,5,20,1,"-",$color[rand(1,4)]);
	}
	//绘制第二个数
	imagestring($image,5,28,1,$number2,$color[rand(1,4)]);
	header("Content-type:image/png");//这句话用于修改http协议的头部，让浏览器知道这是一张图片
	imagepng($image);//将图片输出
	imagedestroy($image);//销毁服务器端的图片，释放内存
?>