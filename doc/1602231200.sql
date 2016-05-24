ALTER TABLE express ADD pid TINYINT(4) DEFAULT '0' COMMENT '父id';
INSERT INTO `erp`.`express` (`name`, `price`, `pid`) VALUES ('顺丰省内', '13.00', '1');
INSERT INTO `erp`.`express` (`name`, `price`, `pid`) VALUES ('顺丰省外', '23.00', '1');
INSERT INTO `erp`.`express` (`name`, `price`, `pid`) VALUES ('圆通省内', '10.00', '2');
INSERT INTO `erp`.`express` (`name`, `price`, `pid`) VALUES ('圆通省外', '13.00', '2');
ALTER TABLE `channel` ADD `status` TINYINT(4) DEFAULT '0' COMMENT '状态：0正常，1：隐藏';
INSERT INTO `erp`.`menu` (`name`, `con`, `act`, `sort`) VALUES ('结算申请', 'account', 'index', '13');