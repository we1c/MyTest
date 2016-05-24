UPDATE goods SET platform = 1 WHERE `status` IN (2,3,4);
ALTER TABLE goods ADD former_platform TINYINT(4) DEFAULT '0' COMMENT '改变前的platform';