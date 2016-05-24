CREATE TABLE `orders_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL COMMENT '订单id',
  `price_freight` decimal(10,2) DEFAULT '0.00' COMMENT '统一运费',
  `real_freight` decimal(10,2) DEFAULT '0.00' COMMENT '实付运费',
  `price_certificate` decimal(10,2) DEFAULT '0.00' COMMENT '证书费用',
  `real_certificate` decimal(10,2) DEFAULT '0.00' COMMENT '实付证书费用',
  `price_pack` decimal(10,2) DEFAULT '0.00' COMMENT '包装费用',
  `real_pack` decimal(10,2) DEFAULT '0.00' COMMENT '实付包装费用',
  `price_mount` decimal(10,2) DEFAULT '0.00' COMMENT '装裱费用',
  `real_mount` decimal(10,2) DEFAULT '0.00' COMMENT '实付装裱费用',
  `price_other` decimal(10,2) DEFAULT '0.00' COMMENT '其他费用',
  `real_other` decimal(10,2) DEFAULT '0.00' COMMENT '实付其他费用',
  `updateTime` int(11) DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
