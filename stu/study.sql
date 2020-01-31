# Host: localhost  (Version: 5.5.53)
# Date: 2019-03-18 15:10:55
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "stu"
#

CREATE TABLE `stu` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `sid` char(20) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `age` int(6) NOT NULL,
  `sex` varchar(20) DEFAULT NULL,
  `class` varchar(50) NOT NULL,
  `chinese` int(11) DEFAULT NULL,
  `math` int(11) DEFAULT NULL,
  `english` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

#
# Data for table "stu"
#

INSERT INTO `stu` VALUES (11,'081217113','齐白石',23,'男','三年级',96,56,32),(13,'081217111','鸿钧',22,'男','一年级',95,99,90),(16,'081217115','youyou',45,'男','五年级',23,54,88),(17,'081217112','gh',111,'男','一年级',65,45,46),(18,'081217116','pp',12,'女','六年级',11,99,77),(19,'081217114','呼啦啦',9,'男','一年级',85,100,39),(20,'081217117','恰什',6,'女','二年级',100,100,100);

#
# Structure for table "user"
#

CREATE TABLE `user` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `is_admin` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

#
# Data for table "user"
#

INSERT INTO `user` VALUES (5,'yy','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',1),(6,'王安石','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',0),(7,'嬴政','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',0),(8,'鸿钧','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',0),(9,'齐白石','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',0),(10,'爱德华','356a192b7913b04c54574d18c28d46e6395428ab',0),(11,'杰克','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',0),(12,'haha','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',0),(13,'admin','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d',1);
