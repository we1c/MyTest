ALTER TABLE express ADD price DECIMAL(10,2) DEFAULT '0' COMMENT '统一运费';
INSERT INTO `erp`.`bank` (`name`, `bank`, `sort`) VALUES ('招商银行', '110914042110801', '0');
INSERT INTO `erp`.`pay_bank` (`tid`, `bid`, `sort`) VALUES ('2', '4', '0');
INSERT INTO `erp`.`pay_bank` (`tid`, `bid`, `sort`) VALUES ('3', '4', '0');
INSERT INTO `erp`.`pay_bank` (`tid`, `bid`, `sort`) VALUES ('4', '4', '0');