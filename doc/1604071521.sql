/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50527
Source Host           : localhost:3306
Source Database       : erp

Target Server Type    : MYSQL
Target Server Version : 50527
File Encoding         : 65001

Date: 2016-04-07 15:21:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for goods_templet
-- ----------------------------
DROP TABLE IF EXISTS `goods_templet`;
CREATE TABLE `goods_templet` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `main_title` varchar(255) DEFAULT NULL COMMENT '主标题',
  `small_title` varchar(255) DEFAULT NULL COMMENT '副标题',
  `cat_id` int(10) unsigned NOT NULL COMMENT '类目id',
  `is_img` enum('0','1') DEFAULT NULL COMMENT '0：引入商品图片，1：不引入商品图片',
  `is_spec` enum('0','1') DEFAULT NULL COMMENT '0：引入商品规格，1：不引入商品规格',
  `info` text COMMENT '描述',
  `createtime` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `edittime` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  `creator` char(11) DEFAULT NULL COMMENT '创建者',
  `editor` char(11) DEFAULT NULL COMMENT '更新者',
  `sort` int(10) unsigned DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
