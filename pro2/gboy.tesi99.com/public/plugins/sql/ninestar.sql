SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE TABLE IF NOT EXISTS `star_activate_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级用户ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被激活用户',
  `phone` char(11) NOT NULL COMMENT '用户手机号',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '激活时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='激活日志表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `star_apply_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请人ID',
  `level` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '申请等级',
  `leaderid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核人ID',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0审核中 1审核通过',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  `utime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='申请记录表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `star_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐人ID',
  `path_id` text NOT NULL COMMENT '推荐路径ID',
  `level` tinyint(2) NOT NULL DEFAULT '0' COMMENT '用户等级',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表' AUTO_INCREMENT=7 ;

--
-- 转存表中的数据 `star_user`
--

INSERT INTO `star_user` (`id`, `pid`, `path_id`, `level`, `phone`, `created`) VALUES
(6, 0, ',0,1,', 0, '15817306354', 1551780585);