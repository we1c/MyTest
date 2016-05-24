alter table `orders` ADD priceAnomaly TINYINT (4) DEFAULT '0' comment '价格是否异常：1异常 0正常';
alter table sell_plan modify column express decimal(10,2) default '5' comment '快递';
alter table sell_plan modify column certificate decimal(10,2) default '8' comment '证书';
alter table sell_plan modify column package decimal(10,2) default '12' comment '包装';