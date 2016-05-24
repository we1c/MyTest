
ALTER TABLE app_version ADD fileName VARCHAR(100) DEFAULT '' COMMENT '文件名';
ALTER TABLE goods ADD isChannel TINYINT(4) DEFAULT '0' COMMENT '是否接受分销：0是，1否';