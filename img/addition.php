<?php
/*  100以内加减法验证码图片版v2.0.0.0006  */
/*     版权归星星站点所有，保留所有权利    */
/*                             by 星星                          */
/*                 转载请保留以上信息                 */
	header('Cache-Control: no-cache, must-revalidate');//禁用缓存
	session_start();//为后面将数据保存到session做准备
	$name=isset($_GET['name'])?$_GET['name']:'addition';//存放名称

	$number1=rand(30,70);//生成第一个数字（伪随机数）
	$number2=rand(0,30);//生产第二个数字
	$operator=rand(0,1);//生成运算符，0表示+，1表示-

	$image=imagecreatetruecolor(50,20);//创建画布（背景为黑色）
	//定义随机颜色
	$color=[
		imagecolorallocate($image,255,0,0),
		imagecolorallocate($image,0,255,0),
		imagecolorallocate($image,255,255,0),
		imagecolorallocate($image,20,208,255),
		imagecolorallocate($image,150,150,255),
		imagecolorallocate($image,255,150,50),
		imagecolorallocate($image,255,255,255),
		imagecolorallocate($image,255,150,150),
		imagecolorallocate($image,200,200,200)
	];
	$max_color_num=count($color)-1;

	//通过for循环创建干扰机器人的线条，这里生成十条
	for($i=0;$i<20;$i++){
		$startx=rand(0,50);
		$starty=rand(0,20);
		//使用随机颜色在画布的随机位置绘制直线
		imageline($image,$startx,$starty,$startx+rand(-4,4),$starty+rand(-2,2),$color[rand(0,$max_color_num)]);
	}

	//将第一个数绘制到画布
	imagettftext($image,rand(14,16),rand(-5,5),rand(-2,0),rand(16,19),$color[rand(0,$max_color_num)],'arial.ttf',$number1);
	//判断运算符
	if($operator==0){
		//加法
		$_SESSION[$name]=$number1+$number2;//将结果保存至session
		imagettftext($image,rand(16,18),rand(-5,5),rand(18,20),rand(16,19),$color[rand(0,$max_color_num)],'arial.ttf','+');
	}else{
		//减法（详细同上）
		$_SESSION[$name]=$number1-$number2;
		imagettftext($image,rand(24,25),rand(-5,5),rand(18,20),rand(16,19),$color[rand(0,$max_color_num)],'arial.ttf','-');
	}
	//绘制第二个数
	imagettftext($image,rand(14,16),rand(-5,5),rand(28,30),rand(16,19),$color[rand(0,$max_color_num)],'arial.ttf',$number2);

	header("Content-type:image/png");//这句话用于修改http协议的头部，让浏览器知道这是一张图片
	imagepng($image);//将图片输出
	imagedestroy($image);//销毁服务器端的图片，释放内存
?>