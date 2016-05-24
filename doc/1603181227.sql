
ALTER TABLE channel DROP column ctimes;
CREATE TABLE `channel_shop_ctimes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channelId` int(11) NOT NULL COMMENT '分销商id',
  `shopId` int(11) NOT NULL COMMENT '店铺id',
  `ctimes` decimal(10,2) DEFAULT '1.00' COMMENT '渠道倍数',
  `updateTime` int(11) DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;