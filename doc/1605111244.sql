alter table channel add clearing_type TINYINT(4) default '1' comment '结算方式：1-先款后货，2-先货后款';
alter table channel add credit_limit int(11) default '0' comment '授信额度';
alter table channel add used_limit int(11) default '0' comment '已用额度';
alter table orders add beyond_limit tinyint(4) default '0' comment '是否超出授信额度：0-未超出，1-超出';