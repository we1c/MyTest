
UPDATE `erp`.`menu` SET `name`='产品开发' WHERE (`con`='shopmarket');
UPDATE `erp`.`menu` SET `name`='正常销售' WHERE (`con`='platf');

ALTER TABLE shop ADD quota int(11)  COMMENT '包邮限额';