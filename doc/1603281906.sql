CREATE TABLE `cost` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `costName` char(30) NOT NULL DEFAULT '' COMMENT '费用名称',
  `price` decimal(10,2) DEFAULT '0.00',
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cost
-- ----------------------------
INSERT INTO `cost` VALUES ('1', '物流费用', '0.00', '0');
INSERT INTO `cost` VALUES ('2', '证书费用', '0.00', '0');
INSERT INTO `cost` VALUES ('3', '包装费用', '0.00', '0');
INSERT INTO `cost` VALUES ('4', '卖家包邮', '0.00', '1');
INSERT INTO `cost` VALUES ('5', '普通快递', '12.00', '1');
INSERT INTO `cost` VALUES ('6', '顺丰快递', '23.00', '1');
INSERT INTO `cost` VALUES ('7', '无需证书', '0.00', '2');
INSERT INTO `cost` VALUES ('8', '普通证书', '10.00', '2');
INSERT INTO `cost` VALUES ('9', '中等证书', '20.00', '2');
INSERT INTO `cost` VALUES ('10', '国检证书', '200.00', '2');
INSERT INTO `cost` VALUES ('11', '免包装费', '0.00', '3');
INSERT INTO `cost` VALUES ('12', '普通包装', '10.00', '3');
INSERT INTO `cost` VALUES ('13', '精致包装', '30.00', '3');
