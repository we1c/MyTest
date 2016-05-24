ALTER TABLE account_shop ADD audit_note VARCHAR(255) COMMENT '审核人备注';
ALTER TABLE account_shop ADD audit_status TINYINT(4) DEFAULT '0' COMMENT '审核状态: 0: 未审核，1已审核 ，2审核异常';
ALTER TABLE account_shop ADD type TINYINT(4) COMMENT '结算类型：1供货商结算';
ALTER TABLE account_shop ADD auditTime INT(11) DEFAULT '0' COMMENT '审核时间';