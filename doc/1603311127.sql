DROP TABLE IF EXISTS `node`;
CREATE TABLE `node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '节点名称',
  `con` varchar(50) COMMENT '控制器名称',
  `act` varchar(50) COMMENT '方法名称',
  `pid` int(11) NOT NULL COMMENT '父菜单id',
  `sort` int(11) COMMENT '排序',
  `level` int(11) COMMENT '等级',
  `isMenu` int(11) COMMENT '是否为菜单：1是，0否',
  `status` int(11) COMMENT '状态:1启用，0禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='节点表';

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '角色名称',
  `pid` int(11) NOT NULL COMMENT '父角色id',
  `sort` int(11) COMMENT '排序',
  `status` int(11) COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色表';

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `role_id` int(11) NOT NULL COMMENT '角色id',  
  KEY `role_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户-角色表';

DROP TABLE IF EXISTS `role_node`;
CREATE TABLE `role_node` (
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `node_id` int(11) NOT NULL COMMENT '节点id',
  KEY `role_id` (`role_id`),
  KEY `node_id` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色-节点表';

INSERT INTO `node` VALUES ('1', '供销模块', '', '', '0', '1', '1', '1', '1');
INSERT INTO `node` VALUES ('2', '平台模块', '', '', '0', '2', '1', '1', '1');
INSERT INTO `node` VALUES ('3', '审核模块', '', '', '0', '3', '1', '1', '1');
INSERT INTO `node` VALUES ('4', '订单模块', '', '', '0', '4', '1', '1', '1');
INSERT INTO `node` VALUES ('5', '财务模块', '', '', '0', '5', '1', '1', '1');
INSERT INTO `node` VALUES ('6', '系统模块', '', '', '0', '6', '1', '1', '1');
INSERT INTO `node` VALUES ('7', '供应商管理', 'supplier', 'index', '1', '1', '2', '1', '1');
INSERT INTO `node` VALUES ('8', '店铺管理', 'shop', 'index', '1', '2', '2', '1', '1');
INSERT INTO `node` VALUES ('9', '分销商管理', 'distributor', 'index', '1', '3', '2', '1', '1');
INSERT INTO `node` VALUES ('10', '类目管理', 'category', 'index', '2', '1', '2', '1', '1');
INSERT INTO `node` VALUES ('11', '商品管理', 'goods', 'index', '2', '2', '2', '1', '1');
INSERT INTO `node` VALUES ('12', '产品开发', 'shopmarket', 'index', '2', '3', '2', '1', '1');
INSERT INTO `node` VALUES ('13', '正常销售', 'platf', 'index', '2', '4', '2', '1', '1');
INSERT INTO `node` VALUES ('14', '推送商品', 'push', 'index', '2', '5', '2', '1', '1');
INSERT INTO `node` VALUES ('15', '任务中心', 'checkone', 'index', '3', '1', '2', '1', '1');
INSERT INTO `node` VALUES ('16', '审核中心', 'checktwo', 'index', '3', '2', '2', '1', '1');
INSERT INTO `node` VALUES ('17', '推送审核', 'checkpush', 'index', '3', '3', '2', '1', '1');
INSERT INTO `node` VALUES ('18', '订单管理', 'order', 'index', '4', '1', '2', '1', '1');
INSERT INTO `node` VALUES ('19', '发货管理', 'deliver', 'index', '4', '2', '2', '1', '1');
INSERT INTO `node` VALUES ('20', '结算申请', 'account', 'index', '5', '1', '2', '1', '1');
INSERT INTO `node` VALUES ('21', '财务审核', 'audit', 'index', '5', '2', '2', '1', '1');
INSERT INTO `node` VALUES ('22', '报表管理', 'report', 'index', '5', '3', '2', '1', '1');
INSERT INTO `node` VALUES ('23', '角色管理', 'role', 'index', '6', '1', '2', '1', '1');
INSERT INTO `node` VALUES ('24', '员工管理', 'admin', 'index', '6', '2', '2', '1', '1');
INSERT INTO `node` VALUES ('25', '节点管理', 'node', 'index', '6', '3', '2', '1', '1');
INSERT INTO `node` VALUES ('26', '操作日志', 'blog', 'index', '6', '4', '2', '1', '1');
INSERT INTO `node` VALUES ('27', '版本管理', 'version', 'index', '6', '5', '2', '1', '1');

INSERT INTO `erp`.`node` ( `name`, `con`, `act` , `sort`, `pid` , `level` , `isMenu`, `status` ) 
VALUES  
( '供应商列表', 'supplier', 'index', '1', '7', '3', '0', '1' ) ,
( '编辑供应商', 'supplier', 'edit', '2', '7', '3', '0', '1' ) ,
( '查看供应商', 'supplier', 'view', '3', '7', '3', '0', '1' ) ,
( '激活供应商', 'supplier', 'enable', '4', '7', '3', '0', '1' ) ,
( '禁用供应商', 'supplier', 'disable', '5', '7', '3', '0', '1' ) ,
( '添加供应商', 'supplier', 'add', '6', '7', '3', '0', '1' ) , 

( '店铺列表', 'shop', 'index', '1', '8', '3', '0', '1' ) ,
( '编辑店铺', 'shop', 'edit', '2', '8', '3', '0', '1' ) ,
( '禁用店铺', 'shop', 'disable', '3', '8', '3', '0', '1' ) ,
( '开启店铺', 'shop', 'enable', '4', '8', '3', '0', '1' ) ,
( '获取店铺城市', 'shop', 'city', '5', '8', '3', '0', '1' ) ,
( '获取店铺地区', 'shop', 'area', '6', '8', '3', '0', '1' ) ,
( '获取店铺每日新增商品数', 'shop', 'numday', '7', '8', '3', '0', '1' ) ,
( '上传店铺头像', 'shop', 'uploadheadimg', '8', '8', '3', '0', '1' ) ,
( '添加店铺', 'shop', 'add', '9', '8', '3', '0', '1' ) ,

( '分销商列表', 'distributor', 'index', '1', '9', '3', '0', '1' ) ,
( '编辑分销商', 'distributor', 'edit', '2', '9', '3', '0', '1' ) ,
( '删除分销商', 'distributor', 'del', '3', '9', '3', '0', '1' ) ,
( '启用分销商', 'distributor', 'open', '4', '9', '3', '0', '1' ) ,
( '添加分销商', 'distributor', 'add', '5', '9', '3', '0', '1' ) ,

( '类目列表', 'category', 'index', '1', '10', '3', '0', '1' ) ,
( '编辑类目', 'category', 'edit', '2', '10', '3', '0', '1' ) ,
( '删除类目', 'category', 'delCat', '3', '10', '3', '0', '1' ) ,
( '获取类目详情', 'category', 'getInfoById', '4', '10', '3', '0', '1' ) ,
( '获取类目参数', 'category', 'getAttrByCatId', '5', '10', '3', '0', '1' ) ,
( '添加类目', 'category', 'add', '6', '10', '3', '0', '1' ) ,

( '商家商品列表', 'goods', 'index', '1', '11', '3', '0', '1' ) ,
( '编辑商品', 'goods', 'edit', '2', '11', '3', '0', '1' ) ,
( '停售商品', 'goods', 'pause', '3', '11', '3', '0', '1' ) ,
( '上架商品', 'goods', 'restore', '4', '11', '3', '0', '1' ) ,
( '售出商品', 'goods', 'delStock', '5', '11', '3', '0', '1' ) ,
( '补货商品', 'goods', 'addStock', '6', '11', '3', '0', '1' ) ,
( '获取类目', 'goods', 'category', '7', '11', '3', '0', '1' ) ,
( '改变商品状态', 'goods', 'change', '8', '11', '3', '0', '1' ) ,
( '下架商品', 'goods', 'downGoods', '9', '11', '3', '0', '1' ) ,
( '删除商品', 'goods', 'delGoods', '10', '11', '3', '0', '1' ) ,
( '删除商品照片', 'goods', 'delOldImg', '11', '11', '3', '0', '1' ) ,
( '申请分销', 'goods', 'apply', '12', '11', '3', '0', '1' ) ,
( '获取商品参数', 'goods', 'getPara', '13', '11', '3', '0', '1' ) ,
( '获取商品价格', 'goods', 'setprice', '14', '11', '3', '0', '1' ) ,
( '获取商品数目', 'goods', 'expcount', '15', '11', '3', '0', '1' ) ,
( '导出商品带图片', 'goods', 'exportgImg', '16', '11', '3', '0', '1' ) ,
( '导出商品无图片', 'goods', 'exportg', '17', '11', '3', '0', '1' ) ,
( '导出所有商品', 'goods', 'exportallgoods', '18', '11', '3', '0', '1' ) ,
( '删除已售出的推送商品', 'goods', 'delSellGoods', '19', '11', '3', '0', '1' ) ,
( '平台商品进审核', 'goods', 'goodsToCheck', '20', '11', '3', '0', '1' ) ,
( '添加商品', 'goods', 'add', '21', '11', '3', '0', '1' ) ,

( '产品开发商品列表', 'shopmarket', 'index', '1', '12', '3', '0', '1' ) ,
( '批量推送商品', 'shopmarket', 'batchPush', '2', '12', '3', '0', '1' ) ,
( '导出商品', 'shopmarket', 'export', '3', '12', '3', '0', '1' ) ,
( '编辑商品', 'shopmarket', 'edit', '4', '12', '3', '0', '1' ) ,
( '推送商品', 'shopmarket', 'pushGoods', '5', '12', '3', '0', '1' ) ,

( '正常销售商品列表', 'platf', 'index', '1', '13', '3', '0', '1' ) ,
( '编辑商品', 'platf', 'edit', '2', '13', '3', '0', '1' ) ,
( '获取商品类目', 'platf', 'category', '3', '13', '3', '0', '1' ) ,
( '改变商品状态', 'platf', 'change', '4', '13', '3', '0', '1' ) ,
( '上传商品图片', 'platf', 'upfile', '5', '13', '3', '0', '1' ) ,
( '获取商品参数', 'platf', 'getPara', '6', '13', '3', '0', '1' ) ,
( '推送商品', 'platf', 'pushGoods', '7', '13', '3', '0', '1' ) ,
( '上下架', 'platf', 'updateGoods', '8', '13', '3', '0', '1' ) ,
( '批量推送商品', 'platf', 'batchPush', '9', '13', '3', '0', '1' ) ,
( '获取商品图片', 'platf', 'getgoodsimgbyid', '10', '13', '3', '0', '1' ) ,
( '添加商品', 'platf', 'add', '11', '13', '3', '0', '1' ) ,

( '推送商品列表', 'push', 'index', '1', '14', '3', '0', '1' ) ,
( '批量移除推送商品', 'push', 'batchDel', '2', '14', '3', '0', '1' ) ,
( '推送商品', 'push', 'pushGoods', '3', '14', '3', '0', '1' ) ,
( '预售商品', 'push', 'presell', '4', '14', '3', '0', '1' ) ,
( '编辑商品', 'push', 'edit', '5', '14', '3', '0', '1' ) ,
( '下单', 'push', 'addOrder', '6', '14', '3', '0', '1' ) ,
( '获取支付类型', 'push', 'getPayType', '7', '14', '3', '0', '1' ) ,
( '获取收款账号', 'push', 'getPayBank', '8', '14', '3', '0', '1' ) ,
( '获取城市列表', 'push', 'city', '9', '14', '3', '0', '1' ) ,
( '获取地区列表', 'push', 'area', '10', '14', '3', '0', '1' ) ,
( '导出数据', 'push', 'export', '11', '14', '3', '0', '1' ) ,
( '导出数据', 'push', 'exportg', '12', '14', '3', '0', '1' ) ,
( '上架价格异常商品', 'push', 'upGoods', '13', '14', '3', '0', '1' ) ,
( '销售计划', 'push', 'sellPlan', '14', '14', '3', '0', '1' ) ,
( '移除推送商品', 'push', 'del', '15', '14', '3', '0', '1' ) ,

( '任务中心列表', 'checkone', 'index', '1', '15', '3', '0', '1' ) ,
( '编辑商品', 'checkone', 'edit', '2', '15', '3', '0', '1' ) ,
( '获取商品类目', 'checkone', 'category', '3', '15', '3', '0', '1' ) ,
( '获取商品参数', 'checkone', 'getPara', '4', '15', '3', '0', '1' ) ,
( '审核', 'checkone', 'check', '5', '15', '3', '0', '1' ) ,
( '删除审核及商品', 'checkone', 'del', '6', '15', '3', '0', '1' ) ,
( '添加商品', 'checkone', 'add', '7', '15', '3', '0', '1' ) ,

( '审核中心列表', 'checktwo', 'index', '1', '16', '3', '0', '1' ) ,
( '获取描述', 'checktwo', 'getIntro', '2', '16', '3', '0', '1' ) ,
( '编辑描述', 'checktwo', 'saveIntro', '3', '16', '3', '0', '1' ) ,
( '审核商品', 'checktwo', 'change', '4', '16', '3', '0', '1' ) ,
( '批量审核商品', 'checktwo', 'batchChange', '5', '16', '3', '0', '1' ) ,
( '编辑商品', 'checktwo', 'edit', '6', '16', '3', '0', '1' ) ,
( '获取商品类目', 'checktwo', 'category', '7', '16', '3', '0', '1' ) ,
( '获取商品参数', 'checktwo', 'getPara', '8', '16', '3', '0', '1' ) ,
( '获取商品参数', 'checktwo', 'getCurrentAllAttrs', '9', '16', '3', '0', '1' ) ,
( '审核商品', 'checktwo', 'check', '10', '16', '3', '0', '1' ) ,
( '删除审核商品', 'checktwo', 'del', '11', '16', '3', '0', '1' ) ,
( '编辑商品', 'checktwo', 'singleSave', '12', '16', '3', '0', '1' ) ,

( '推送审核列表', 'checkpush', 'index', '1', '17', '3', '0', '1' ) ,
( '获取商品类目', 'checkpush', 'category', '2', '17', '3', '0', '1' ) ,
( '获取商品参数', 'checkpush', 'getPara', '3', '17', '3', '0', '1' ) ,
( '审核商品', 'checkpush', 'check', '4', '17', '3', '0', '1' ) ,
( '编辑商品', 'checkpush', 'edit', '5', '17', '3', '0', '1' ) ,

( '订单列表', 'order', 'index', '1', '18', '3', '0', '1' ) ,
( '店铺下的商品列表', 'order', 'getGoods', '2', '18', '3', '0', '1' ) ,
( '商品详情', 'order', 'getGoodsInfo', '3', '18', '3', '0', '1' ) ,
( '快递查询', 'order', 'getExpress', '4', '18', '3', '0', '1' ) ,
( '订单详情', 'order', 'detail', '5', '18', '3', '0', '1' ) ,
( '发货', 'order', 'deliver', '6', '18', '3', '0', '1' ) ,
( '编辑订单', 'order', 'edit', '7', '18', '3', '0', '1' ) ,
( '获取付款类型', 'order', 'getPayType', '8', '18', '3', '0', '1' ) ,
( '获取收款账号', 'order', 'getPayBank', '9', '18', '3', '0', '1' ) ,
( '快递公司列表', 'order', 'getAllExpress', '10', '18', '3', '0', '1' ) ,
( '收货', 'order', 'takes', '11', '18', '3', '0', '1' ) ,
( '退货', 'order', 'reback', '12', '18', '3', '0', '1' ) ,
( '城市列表', 'order', 'city', '13', '18', '3', '0', '1' ) ,
( '地区列表', 'order', 'area', '14', '18', '3', '0', '1' ) ,
( '导出数据', 'order', 'exporttext', '15', '18', '3', '0', '1' ) ,
( '添加订单', 'order', 'add', '16', '18', '3', '0', '1' ) ,

( '发货列表', 'deliver', 'index', '1', '19', '3', '0', '1' ) ,
( '店铺下的商品列表', 'deliver', 'getGoods', '2', '19', '3', '0', '1' ) ,
( '商品详情', 'deliver', 'getGoodsInfo', '3', '19', '3', '0', '1' ) ,
( '快递查询', 'deliver', 'cancel', '4', '19', '3', '0', '1' ) ,
( '订单详情', 'deliver', 'detail', '5', '19', '3', '0', '1' ) ,
( '发货', 'deliver', 'deliver', '6', '19', '3', '0', '1' ) ,
( '编辑订单', 'deliver', 'edit', '7', '19', '3', '0', '1' ) ,
( '获取付款类型', 'deliver', 'getPayType', '8', '19', '3', '0', '1' ) ,
( '获取收款账号', 'deliver', 'getPayBank', '9', '19', '3', '0', '1' ) ,
( '快递公司列表', 'deliver', 'getAllExpress', '10', '19', '3', '0', '1' ) ,
( '收货', 'deliver', 'takes', '11', '19', '3', '0', '1' ) ,
( '退货', 'deliver', 'reback', '12', '19', '3', '0', '1' ) ,
( '城市列表', 'deliver', 'city', '13', '19', '3', '0', '1' ) ,
( '地区列表', 'deliver', 'area', '14', '19', '3', '0', '1' ) ,
( '导出数据', 'deliver', 'exporttext', '15', '19', '3', '0', '1' ) ,
( '删除发货单', 'deliver', 'del', '16', '19', '3', '0', '1' ) ,

( '结算单列表', 'account', 'index', '1', '20', '3', '0', '1' ) ,
( '删除申请单', 'account', 'del', '2', '20', '3', '0', '1' ) ,
( '结算订单详情', 'account', 'detail', '3', '20', '3', '0', '1' ) ,
( '导出数据', 'account', 'exportExcel', '4', '20', '3', '0', '1' ) ,
( '添加申请单', 'account', 'add', '5', '20', '3', '0', '1' ) ,
( '查询申请单', 'account', 'search', '6', '20', '3', '0', '1' ) ,

( '财务审核列表', 'audit', 'index', '1', '21', '3', '0', '1' ) ,
( '结算订单详情', 'audit', 'view', '2', '21', '3', '0', '1' ) ,
( '审核', 'audit', 'change', '3', '21', '3', '0', '1' ) ,
( '结算订单详情', 'audit', 'detail', '4', '21', '3', '0', '1' ) ,

( '报表管理列表', 'report', 'index', '1', '22', '3', '0', '1' ) ,
( '导出数据', 'report', 'export', '2', '22', '3', '0', '1' ) ,

( '角色列表', 'role', 'index', '1', '23', '3', '0', '1' ) ,
( '删除角色', 'role', 'delete', '2', '23', '3', '0', '1' ) ,
( '添加角色', 'role', 'insert', '3', '23', '3', '0', '1' ) ,
( '编辑角色', 'role', 'edit', '4', '23', '3', '0', '1' ) ,
( '编辑角色', 'role', 'update', '5', '23', '3', '0', '1' ) ,
( '用户列表', 'role', 'developer', '6', '23', '3', '0', '1' ) ,
( '添加用户', 'role', 'adduser', '7', '23', '3', '0', '1' ) ,
( '移除用户', 'role', 'deleterole', '8', '23', '3', '0', '1' ) ,
( '添加角色', 'role', 'add', '9', '23', '3', '0', '1' ) ,

( '员工列表', 'admin', 'index', '1', '24', '3', '0', '1' ) ,
( '编辑员工', 'admin', 'edit', '2', '24', '3', '0', '1' ) ,
( '删除员工', 'admin', 'del', '3', '24', '3', '0', '1' ) ,
( '禁用员工', 'admin', 'disable', '4', '24', '3', '0', '1' ) ,
( '激活员工', 'admin', 'enable', '5', '24', '3', '0', '1' ) ,
( '添加员工', 'admin', 'add', '6', '24', '3', '0', '1' ) ,

( '节点列表', 'node', 'index', '1', '25', '3', '0', '1' ) ,
( '添加节点', 'node', 'add', '2', '25', '3', '0', '1' ) ,
( '编辑节点', 'node', 'edit', '3', '25', '3', '0', '1' ) ,

( '操作日志列表', 'blog', 'index', '1', '26', '3', '0', '1' ) ,

( '版本列表', 'version', 'index', '1', '27', '3', '0', '1' ) ,
( '编辑版本', 'version', 'edit', '2', '27', '3', '0', '1' ) ,
( '删除版本', 'version', 'del', '3', '27', '3', '0', '1' ) ,
( '添加版本', 'version', 'add', '4', '27', '3', '0', '1' ) ;

INSERT INTO `erp`.`role` ( `name`, `pid`, `sort`,`status` ) 
VALUES  
( '超级管理员', '14','1', '1' ) ,
( '市场部', '0','3', '1' ) ,
( '商品部', '0','9', '1' ) ,
( '京办', '0','13', '1' ) ,

( '管理员', '1','2', '1' ) ,

( '黄端总监', '2','4', '1' ) ,
( '绿端经理', '3','10', '1' ) ,

( '红端经理', '6','5', '1' ) ,
( '红端文案', '6','6', '1' ) ,
( '黄端高级助理', '6','7', '1' ) ,
( '黄端助理', '6','8', '1' ) ,

( '绿端高级助理', '7','11', '1' ) ,
( '绿端助理', '7','12', '1' ) ,

( '开发部', '0','0', '1' ) ;

INSERT INTO `erp`.`user_role` ( `user_id`, `role_id` ) 
VALUES 
( '19', '1' ) ,
( '8', '1' ) ;
