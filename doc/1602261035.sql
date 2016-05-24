CREATE TABLE `account_shop`(
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `shopId` int(11) NOT NULL COMMENT '店铺id',
                `devId` int(11) DEFAULT '0' COMMENT '用户id',
                `total` decimal(10,2) DEFAULT '0' COMMENT '总额',
                `createTime` int(11) DEFAULT '0' COMMENT '创建时间',
                `expectTime` varchar(50) DEFAULT '0' COMMENT '预计支付时间',
                `note` varchar(255) COMMENT '备注',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE orders ADD accountId INT(11) default '0' COMMENT '结算表Id';
ALTER TABLE orders ADD account_status TINYINT(4) default '0' COMMENT '财务审核状态：0 未提交，1已提交 ，2审核通过';
INSERT INTO `erp`.`express` (`name`, `price`, `pid`) VALUES ('韵达快递', '0', '0');
INSERT INTO `erp`.`express` (`name`, `price`, `pid`) VALUES ('百世汇通', '0', '0');