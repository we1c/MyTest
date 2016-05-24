INSERT INTO `erp`.`menu` ( `name`, `con`, `act`, `sort`) VALUES ( '报表管理', 'report', 'index', '14');
ALTER TABLE orders_price ADD price_fluctuate decimal(10,2) DEFAULT '0' COMMENT '价格波动';
ALTER TABLE orders_price ADD price_platf decimal(10,2) DEFAULT '0' COMMENT '平台费';
ALTER TABLE orders_price ADD price_transfer decimal(10,2) DEFAULT '0' COMMENT '转账费';