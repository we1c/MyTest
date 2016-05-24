DROP TABLE IF EXISTS `sell_plan`;
CREATE TABLE `sell_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startTime` int(11) NOT NULL DEFAULT '0' COMMENT '预售计划开始时间',
  `endTime` int(11) NOT NULL DEFAULT '0' COMMENT '预售计划结束时间',
  `addPrice` decimal(10,2) DEFAULT '0.00' COMMENT '附加费用',
  `tradeStyle` tinyint(1) unsigned NOT NULL COMMENT '交易方式。0为竞买，1为一口价。',
  `tradePrice` decimal(10,2) DEFAULT '0.00' COMMENT '单件交易价格。若为竞买模式，则为起拍价',
  `tradeCount` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '交易数量。当为竞买模式，为1。',
  `channelPrice` decimal(10,2) DEFAULT '0.00' COMMENT '渠道价格',
  `express` int(11) unsigned NOT NULL DEFAULT '5' COMMENT '快递',
  `package` int(11) unsigned NOT NULL DEFAULT '12' COMMENT '包装费用',
  `certificate` int(11) unsigned NOT NULL DEFAULT '10' COMMENT '证书费用（默认10（普通证书））',
  `startUpTime` int(11) NOT NULL DEFAULT '0' COMMENT '开始上架时间',
  `goodsId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
