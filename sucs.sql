-- Host: localhost
-- Generation Time: 2016-02-08 13:30:15
-- 服务器版本： 5.6.25
-- PHP Version: 7.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `sucs`
--

-- --------------------------------------------------------

--
-- 表的结构 `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` mediumint(8) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL,
  `pid` tinyint(3) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- 表的结构 `admin_pages`
--

DROP TABLE IF EXISTS `admin_pages`;
CREATE TABLE IF NOT EXISTS `admin_pages` (
  `id` smallint(5) unsigned NOT NULL,
  `pid` smallint(5) unsigned NOT NULL,
  `title` varchar(10) NOT NULL,
  `is_menu` tinyint(1) NOT NULL,
  `src` varchar(200) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `admin_pages`
--

INSERT INTO `admin_pages` (`id`, `pid`, `title`, `is_menu`, `src`) VALUES
(2, 0, '用户管理', 1, NULL),
(10, 2, '用户列表', 0, NULL),
(11, 2, '在线用户', 0, NULL),
(3, 0, '消息管理', 0, NULL),
(12, 2, '邀请码管理', 0, NULL),
(4, 0, '应用管理', 0, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `apps`
--

DROP TABLE IF EXISTS `apps`;
CREATE TABLE IF NOT EXISTS `apps` (
  `aid` tinyint(4) NOT NULL,
  `name` varchar(20) NOT NULL DEFAULT '',
  `urlroot` varchar(100) NOT NULL DEFAULT '',
  `key` char(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- 表的结构 `exc_log`
--

CREATE TABLE IF NOT EXISTS `exc_log` (
  `timeid` bigint(10) unsigned zerofill NOT NULL,
  `uid` mediumint(9) NOT NULL,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `number` smallint(6) NOT NULL,
  `why` varchar(50) COLLATE utf8_bin NOT NULL,
  `isread` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `filelogin`
--

DROP TABLE IF EXISTS `filelogin`;
CREATE TABLE IF NOT EXISTS `filelogin` (
  `logid` int(10) unsigned NOT NULL,
  `key` varchar(10) NOT NULL,
  `file_md5` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `forbidden_ip`
--

DROP TABLE IF EXISTS `forbidden_ip`;
CREATE TABLE IF NOT EXISTS `forbidden_ip` (
  `id` tinyint(3) unsigned NOT NULL,
  `adder` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(9) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `why` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `friends`
--

DROP TABLE IF EXISTS `friends`;
CREATE TABLE IF NOT EXISTS `friends` (
  `id` mediumint(8) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL,
  `fuid` mediumint(8) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `group`
--

DROP TABLE IF EXISTS `group`;
CREATE TABLE IF NOT EXISTS `group` (
  `gid` tinyint(3) unsigned NOT NULL,
  `gname` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '普通用户',
  `read_rank` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `color` char(6) COLLATE utf8_bin NOT NULL DEFAULT '000000',
  `use_honor` tinyint(1) NOT NULL DEFAULT '0',
  `max_signature` smallint(5) unsigned NOT NULL DEFAULT '0',
  `use_title` tinyint(1) NOT NULL DEFAULT '0',
  `is_vip` tinyint(1) NOT NULL DEFAULT '0',
  `get` smallint(3) NOT NULL DEFAULT '0' COMMENT '非vip组表示积分下限，vip组表示获得方式的id。'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- 转存表中的数据 `group`
--

INSERT INTO `group` (`gid`, `gname`, `read_rank`, `color`, `use_honor`, `max_signature`, `use_title`, `is_vip`, `get`) VALUES
(0, '系统管理员', 255, '000000', 1, 2000, 1, 1, 0),
(16, '普通用户', 10, '1', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `logins`
--

DROP TABLE IF EXISTS `logins`;
CREATE TABLE IF NOT EXISTS `logins` (
  `id` int(11) NOT NULL,
  `key` char(10) COLLATE utf8_bin NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(9) NOT NULL,
  `username` varchar(10) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `UA` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `ip` varchar(15) COLLATE utf8_bin DEFAULT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `login_log`
--

DROP TABLE IF EXISTS `login_log`;
CREATE TABLE IF NOT EXISTS `login_log` (
  `time` int(10) unsigned NOT NULL,
  `ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uid` mediumint(9) DEFAULT NULL,
  `username` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `HTTP_USER_AGENT` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `is_true` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- 表的结构 `login_remember`
--

DROP TABLE IF EXISTS `login_remember`;
CREATE TABLE IF NOT EXISTS `login_remember` (
  `id` smallint(6) NOT NULL,
  `key` char(20) CHARACTER SET utf8 NOT NULL,
  `uid` mediumint(9) NOT NULL,
  `end_time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `message`
--

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `mid` int(10) unsigned NOT NULL,
  `sender` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `geter` smallint(6) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `body` varchar(1000) COLLATE utf8_bin NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- --------------------------------------------------------

--
-- 表的结构 `place`
--

DROP TABLE IF EXISTS `place`;
CREATE TABLE IF NOT EXISTS `place` (
  `id` smallint(5) unsigned NOT NULL,
  `name` char(3) NOT NULL,
  `pid` smallint(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `place`
--

INSERT INTO `place` (`id`, `name`, `pid`) VALUES
(1, '中国', 0),
(2, '美国', 0),
(3, '加拿大', 0),
(4, '英国', 0),
(129, '湖南', 1),
(128, '湖北', 1),
(420, '长沙', 129),
(421, '岳阳', 129),
(422, '株洲', 129),
(423, '湘潭', 129);

-- --------------------------------------------------------

--
-- 表的结构 `reg_keys`
--

DROP TABLE IF EXISTS `reg_keys`;
CREATE TABLE IF NOT EXISTS `reg_keys` (
  `key` varchar(100) NOT NULL,
  `end_time` int(10) unsigned NOT NULL DEFAULT '0',
  `number` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `auto` double NOT NULL DEFAULT '1',
  `groups` varchar(500) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- 表的结构 `system`
--

DROP TABLE IF EXISTS `system`;
CREATE TABLE IF NOT EXISTS `system` (
  `key` varchar(20) NOT NULL DEFAULT '',
  `value` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `system`
--

INSERT INTO `system` (`key`, `value`) VALUES
('opening', '1');

-- --------------------------------------------------------

--
-- 表的结构 `uids`
--

DROP TABLE IF EXISTS `uids`;
CREATE TABLE IF NOT EXISTS `uids` (
  `uid` mediumint(9) NOT NULL,
  `key` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `group` tinyint(4) DEFAULT NULL,
  `adder` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 表的结构 `user_egroup`
--

DROP TABLE IF EXISTS `user_egroup`;
CREATE TABLE IF NOT EXISTS `user_egroup` (
 `uid` mediumint(9) NOT NULL,
 `gid` smallint(6) NOT NULL,
 KEY `uid` (`uid`,`gid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `uid` mediumint(8) unsigned NOT NULL,
  `username` varchar(10) COLLATE utf8_bin NOT NULL,
  `password` char(40) COLLATE utf8_bin NOT NULL DEFAULT '',
  `s` char(6) COLLATE utf8_bin NOT NULL DEFAULT '',
  `gid` tinyint(3) unsigned NOT NULL DEFAULT '16',
  `email` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0',
  `reg_ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '127.0.0.1',
  `last_login_time` int(10) unsigned DEFAULT NULL,
  `last_login_ip` varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `signature` varchar(2000) COLLATE utf8_bin DEFAULT NULL,
  `title` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `true_name` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `qq` int(11) DEFAULT NULL,
  `web` varchar(50) COLLATE utf8_bin DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- 转存表中的数据 `users`
--


-- --------------------------------------------------------

--
-- 表的结构 `user_exc`
--

DROP TABLE IF EXISTS `user_exc`;
CREATE TABLE IF NOT EXISTS `user_exc` (
  `uid` mediumint(8) unsigned NOT NULL,
  `经验` int(11) NOT NULL DEFAULT '0',
  `威望` int(11) NOT NULL DEFAULT '0',
  `铜钱` int(11) NOT NULL DEFAULT '5',
  `元宝` int(11) NOT NULL DEFAULT '0',
  `贡献` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_pages`
--
ALTER TABLE `admin_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`aid`);

--
-- Indexes for table `forbidden_ip`
--
ALTER TABLE `forbidden_ip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`);

--
-- Indexes for table `exc_log`
--
ALTER TABLE `exc_log`
  ADD PRIMARY KEY (`timeid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group`
--
ALTER TABLE `group`
  ADD PRIMARY KEY (`gid`);

--
-- Indexes for table `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `login_log`
--
ALTER TABLE `login_log`
  ADD KEY `ip` (`ip`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `login_remember`
--
ALTER TABLE `login_remember`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`mid`),
  ADD KEY `sender` (`sender`),
  ADD KEY `geter` (`geter`);

--
-- Indexes for table `place`
--
ALTER TABLE `place`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reg_keys`
--
ALTER TABLE `reg_keys`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `uids`
--
ALTER TABLE `uids`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `key` (`key`),
  ADD KEY `key_2` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_exc`
--
ALTER TABLE `user_exc`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `admin_pages`
--
ALTER TABLE `admin_pages`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `apps`
--
ALTER TABLE `apps`
  MODIFY `aid` tinyint(4) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `forbidden_ip`
--
ALTER TABLE `forbidden_ip`
  MODIFY `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `group`
--
ALTER TABLE `group`
  MODIFY `gid` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `logins`
--
ALTER TABLE `logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `login_remember`
--
ALTER TABLE `login_remember`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `mid` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `place`
--
ALTER TABLE `place`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_exc`
--
ALTER TABLE `user_exc`
  MODIFY `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;