ALTER TABLE `account_shop` CHANGE `audit_status` `audit_status` TINYINT(4) DEFAULT '0' COMMENT '审核状态: 0: 未审核，1已审核 ，2有驳回审核，3全驳回审核';
ALTER TABLE `orders` CHANGE `sellType` `sellType` TINYINT(4) DEFAULT '0' COMMENT '售出方式：0后台正常订单，1供货商app，2供货商h5，3后台人工售出，4分销商app，5分销商h5';
ALTER TABLE `orders` CHANGE `account_status` `account_status` TINYINT(4) DEFAULT '0' COMMENT '财务审核状态：0 未提交，1已提交 ，2审核通过，3驳回';
INSERT INTO `erp`.`menu` (`name`, `con`, `act`, `sort`) VALUES ('财务审核', 'audit', 'index', '14');
