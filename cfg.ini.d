###########################################################
#		本程序由星星站点制作，禁止用于商业用途		#
###########################################################
#				程序说明					#
###########################################################
# 0、请务必仔细阅读说明
# 1、安装本程序前请设置好数据库的相关配置；
# 2、。。
# 3、如果更换服务器环境请最好手动删除一下.temp文件，我们的程序检测配置文件的变化但不检测服务器环境变化，为防止出现故障强烈建议删除.temp文件强制刷新程序。
# 4、请尽量避免删除.temo文件，它只是配置文件的缓存（因为我们程序读取ini的速度较慢，通过缓存有助于提高速度）

#配置数据库
db_type=mysql		#数据库类型 一般都为mysql，暂时也支持mysql
db_server=127.0.0.1		#数据库主机地址
db_username=root		#数据库用户名
db_password=root		#数据库密码
db_name=sucs		#数据库库名称
#db_prefix=sucs		#数据库表前缀

#debug=1 			#是否开启debug模式

#目录配置
tools_dir=../tools

use_key_reg_oney=0	#注册是否强制要求使用邀请码（目前这个设置没有处理好，起不到作用）
my_script_path=static/myScript2.js#myscript路径（相对index.php或使用绝对路径）

lang_list=zh-cn,zh-tw