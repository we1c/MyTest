/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50527
Source Host           : localhost:3306
Source Database       : erp

Target Server Type    : MYSQL
Target Server Version : 50527
File Encoding         : 65001

Date: 2016-04-07 15:22:28
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for goods_templet_image
-- ----------------------------
DROP TABLE IF EXISTS `goods_templet_image`;
CREATE TABLE `goods_templet_image` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `temp_id` int(10) unsigned DEFAULT NULL COMMENT '模板表id',
  `image` varchar(255) DEFAULT NULL COMMENT '模板图片名称',
  `sort` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
