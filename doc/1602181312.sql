ALTER TABLE channel ADD payway TINYINT(4) default '0' COMMENT '付款方式';
ALTER TABLE channel ADD paybank TINYINT(4) default '0' COMMENT '收款账号';
ALTER TABLE channel ADD headimgurl VARCHAR(200) default NULL COMMENT '头像'; 
ALTER TABLE app_version DROP COLUMN versionCode ;
ALTER TABLE app_version DROP COLUMN dir ; 