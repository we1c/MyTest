ALTER TABLE user_action_log ADD realPrice VARCHAR(11) DEFAULT 0 COMMENT '实付价格';
ALTER TABLE user_action_log ADD number INT(11) DEFAULT 0 COMMENT '数量';
ALTER TABLE orders ADD sellType TINYINT(4) DEFAULT 0 COMMENT '售出方式：0后台正常订单，1app，2后台人工售出';