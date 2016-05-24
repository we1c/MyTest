DROP TABLE IF EXISTS `copywriter_templet`;
CREATE TABLE `copywriter_templet` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `is_title` enum('0','1') DEFAULT NULL COMMENT '0：不引入标题；1：引入标题',
  `is_spec` enum('0','1') DEFAULT NULL COMMENT '0:不引入商品规格；1:引入商品规格',
  `intro` text COMMENT '文案内容',
  `cat_id` int(11) unsigned DEFAULT NULL COMMENT '类目id',
  `createtime` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `edittime` int(11) unsigned DEFAULT NULL COMMENT '编辑时间',
  `creator` char(11) DEFAULT NULL COMMENT '创建者',
  `editor` char(11) DEFAULT NULL COMMENT '编辑者',
  PRIMARY KEY (`id`),
  KEY `catid` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;