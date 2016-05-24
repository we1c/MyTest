alter table app_version add file_md5 VARCHAR(50) default '' comment '文件加密';
alter table app_version add app_type tinyint(4)  comment '文件类型：1-安卓，2-苹果';
alter table app_version add is_must tinyint(4) default '0' comment '强制更新：0-非强制，1-强制';