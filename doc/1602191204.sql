ALTER TABLE orders ADD price_certificate  decimal(10,2) DEFAULT '0.00' COMMENT '证书费用';
ALTER TABLE orders ADD price_pack  decimal(10,2) DEFAULT '0.00' COMMENT '包装费用';
ALTER TABLE orders ADD price_mount  decimal(10,2) DEFAULT '0.00' COMMENT '装裱费用';
ALTER TABLE orders ADD price_other  decimal(10,2) DEFAULT '0.00' COMMENT '其他费用';
ALTER TABLE orders_express ADD real_freight  decimal(10,2) DEFAULT '0.00' COMMENT '实付运费'; 