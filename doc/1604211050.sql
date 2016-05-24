DROP TABLE IF EXISTS `orders_goods`;
CREATE TABLE `orders_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `shop_id` int(11) NOT NULL COMMENT '店铺id',
  `customer_id` int(11) NOT NULL COMMENT '买家id',
  `goods_name` varchar(255) DEFAULT '' COMMENT '商品名称',
  `goods_image` varchar(255) DEFAULT '' COMMENT '商品图片',
  `goods_price` decimal(10,2) DEFAULT '0.00' COMMENT '零售价',
  `goods_number` int(11) DEFAULT '0' COMMENT '数量',
  `goods_pay_price` decimal(10,2) DEFAULT '0.00' COMMENT '成交价',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `goods_id` (`goods_id`) USING BTREE,
  KEY `shop_id` (`shop_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;