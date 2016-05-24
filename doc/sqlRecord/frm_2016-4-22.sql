-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: mysql:3306
-- Generation Time: 2016-04-21 11:27:54
-- 服务器版本： 5.6.26
-- PHP Version: 5.5.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erp`
--

-- --------------------------------------------------------

--
-- 表的结构 `account_shop`
--

CREATE TABLE IF NOT EXISTS `account_shop` (
  `id` int(11) unsigned NOT NULL,
  `shopId` int(11) NOT NULL COMMENT '店铺id',
  `devId` int(11) DEFAULT '0' COMMENT '用户id',
  `total` decimal(10,2) DEFAULT '0.00' COMMENT '总额',
  `createTime` int(11) DEFAULT '0' COMMENT '创建时间',
  `expectTime` varchar(50) DEFAULT '0' COMMENT '预计支付时间',
  `note` varchar(255) DEFAULT NULL COMMENT '备注',
  `audit_note` varchar(255) DEFAULT NULL COMMENT '审核人备注',
  `audit_status` tinyint(4) DEFAULT '0' COMMENT '审核状态: 0: 未审核，1已审核 ，2有驳回审核，3全驳回审核',
  `type` tinyint(4) DEFAULT NULL COMMENT '结算类型：1供货商结算',
  `auditTime` int(11) DEFAULT '0' COMMENT '审核时间',
  `real_total` decimal(10,2) DEFAULT '0.00' COMMENT '申请单实付金额'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `apimss`
--

CREATE TABLE IF NOT EXISTS `apimss` (
  `id` int(10) NOT NULL COMMENT '自增id',
  `phone` varchar(20) NOT NULL,
  `mss` int(10) NOT NULL,
  `updatetime` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信表';

-- --------------------------------------------------------

--
-- 表的结构 `app_version`
--

CREATE TABLE IF NOT EXISTS `app_version` (
  `id` int(11) NOT NULL,
  `descript` text COMMENT '版本描述',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `code` varchar(100) DEFAULT NULL COMMENT '版本号'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `attribute`
--

CREATE TABLE IF NOT EXISTS `attribute` (
  `attr_id` int(10) unsigned NOT NULL COMMENT '属性ID',
  `parent_pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `parent_vid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父值',
  `attr_name` varchar(60) NOT NULL DEFAULT '' COMMENT '属性名称',
  `cat_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属类目',
  `is_key_attr` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否为关键属性',
  `is_sale_attr` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否为关键属性',
  `attr_level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '属性等级',
  `attr_path` varchar(128) NOT NULL DEFAULT '' COMMENT '属性全路径',
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '50' COMMENT '属性排序依据',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示，默认显示',
  `attr_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '属性是否可选: 0 为唯一，1为单选，2为多选',
  `input_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '属性录入方式: 0为手工录入，1为从列表中选择，2为文本域'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `attr_relation`
--

CREATE TABLE IF NOT EXISTS `attr_relation` (
  `id` int(10) unsigned NOT NULL COMMENT '自身ID',
  `attr_name` varchar(128) NOT NULL DEFAULT '' COMMENT '属性名',
  `parent_pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父类名ID',
  `parent_vid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父类值ID',
  `values` varchar(128) NOT NULL DEFAULT '' COMMENT '属性可选值',
  `attr_pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '属性名ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `attr_value`
--

CREATE TABLE IF NOT EXISTS `attr_value` (
  `id` int(10) unsigned NOT NULL COMMENT '自身ID',
  `values` varchar(128) NOT NULL DEFAULT '' COMMENT '属性值',
  `value_alias` varchar(128) NOT NULL DEFAULT '' COMMENT '属性值别名',
  `attr_pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '属性父ID',
  `cat_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属类目'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `bank`
--

CREATE TABLE IF NOT EXISTS `bank` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `bank` varchar(32) NOT NULL COMMENT '银行卡号',
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(10) unsigned NOT NULL COMMENT '商品类别ID',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '商品类别名称',
  `cat_unit` varchar(15) NOT NULL DEFAULT '' COMMENT '单位',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品类别父ID',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '分类等级',
  `cat_path` varchar(128) NOT NULL DEFAULT '' COMMENT '分类全路径',
  `is_attr_pid` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:没属性,1:有属性,默认没有属性',
  `is_parent` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '0:不是父类,1:是父类,默认是父类',
  `cat_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '商品类别描述',
  `cat_sort` tinyint(3) unsigned NOT NULL DEFAULT '50' COMMENT '排序依据',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示，默认显示'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `channel`
--

CREATE TABLE IF NOT EXISTS `channel` (
  `id` int(10) unsigned NOT NULL,
  `userId` int(11) NOT NULL COMMENT '创建者Id',
  `name` varchar(150) NOT NULL COMMENT '渠道名',
  `domain` varchar(150) NOT NULL COMMENT '域名',
  `key` varchar(64) NOT NULL COMMENT 'key值',
  `devId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '后台负责人ID',
  `payway` tinyint(4) DEFAULT '0',
  `paybank` tinyint(4) DEFAULT '0',
  `headimgurl` varchar(200) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '状态：0正常，1：隐藏',
  `apiType` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '接口类型，0:不对接，1:标准，2:对接对方',
  `apiImg` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '接口图片,0:不传图片,1:传图片',
  `apiDown` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '下架商品,0:不通知渠道,1通知渠道'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `channel_shop_ctimes`
--

CREATE TABLE IF NOT EXISTS `channel_shop_ctimes` (
  `id` int(11) unsigned NOT NULL,
  `channelId` int(11) NOT NULL COMMENT '分销商id',
  `shopId` int(11) NOT NULL COMMENT '店铺id',
  `ctimes` decimal(10,2) DEFAULT '1.00' COMMENT '渠道倍数',
  `updateTime` int(11) DEFAULT '0' COMMENT '修改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `channel_view_log`
--

CREATE TABLE IF NOT EXISTS `channel_view_log` (
  `id` int(10) unsigned NOT NULL COMMENT '自增id',
  `jid` int(10) unsigned NOT NULL COMMENT '渠道id',
  `log` text NOT NULL COMMENT '日志',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='渠道访问日志表';

-- --------------------------------------------------------

--
-- 表的结构 `city`
--

CREATE TABLE IF NOT EXISTS `city` (
  `id` int(11) NOT NULL COMMENT '地址ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '地区名字',
  `first_letter` varchar(10) DEFAULT NULL COMMENT '首字母',
  `description` varchar(100) DEFAULT NULL COMMENT '地区路径的cache地址',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '上级路径ID',
  `sort_num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '顺序',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间|信息创建时间',
  `author` int(11) NOT NULL DEFAULT '0' COMMENT '维护人'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='地区|地区数据管理';

-- --------------------------------------------------------

--
-- 表的结构 `copywriter_templet`
--

CREATE TABLE IF NOT EXISTS `copywriter_templet` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `is_title` enum('0','1') DEFAULT NULL COMMENT '0：不引入标题；1：引入标题',
  `is_spec` enum('0','1') DEFAULT NULL COMMENT '0:不引入商品规格；1:引入商品规格',
  `intro` text COMMENT '文案内容',
  `cat_id` int(11) unsigned DEFAULT NULL COMMENT '类目id',
  `createtime` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `edittime` int(11) unsigned DEFAULT NULL COMMENT '编辑时间',
  `creator` char(11) DEFAULT NULL COMMENT '创建者',
  `editor` char(11) DEFAULT NULL COMMENT '编辑者'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `cost`
--

CREATE TABLE IF NOT EXISTS `cost` (
  `id` int(11) unsigned NOT NULL,
  `costName` char(30) NOT NULL DEFAULT '' COMMENT '费用名称',
  `price` decimal(10,2) DEFAULT '0.00',
  `pid` int(11) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `customer`
--

CREATE TABLE IF NOT EXISTS `customer` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(150) DEFAULT '' COMMENT '昵称',
  `tel` char(11) NOT NULL COMMENT '手机号码',
  `openid` char(150) DEFAULT NULL COMMENT '微信openid',
  `nickname` varchar(150) DEFAULT NULL COMMENT '微信昵称',
  `headimgurl` varchar(150) DEFAULT NULL COMMENT '头像'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='买家';

-- --------------------------------------------------------

--
-- 表的结构 `developers`
--

CREATE TABLE IF NOT EXISTS `developers` (
  `id` int(10) unsigned NOT NULL COMMENT 'user id',
  `account` char(11) NOT NULL COMMENT 'email',
  `openId` varchar(150) DEFAULT NULL COMMENT '开发人员openId',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '开发者姓名',
  `pwd` varchar(32) NOT NULL DEFAULT '' COMMENT 'password',
  `createTime` int(11) NOT NULL DEFAULT '0' COMMENT 'register timestamp',
  `loginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `roleId` int(11) NOT NULL,
  `status` enum('0','1') DEFAULT '0',
  `disId` varchar(150) DEFAULT '0' COMMENT '分销商id',
  `sex` enum('0','1','2') DEFAULT NULL COMMENT '0:保密,1:男,2:女',
  `province` int(11) DEFAULT '0' COMMENT '省',
  `city` int(11) DEFAULT '0' COMMENT '市',
  `area` int(11) DEFAULT '0' COMMENT '区',
  `address` varchar(255) DEFAULT '0' COMMENT '详细地址',
  `loginIp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录IP',
  `tel` varchar(11) DEFAULT '0' COMMENT '电话',
  `birthday` varchar(11) DEFAULT '0' COMMENT '生日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台用户表';

-- --------------------------------------------------------

--
-- 表的结构 `distributor`
--

CREATE TABLE IF NOT EXISTS `distributor` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `account` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `createTime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0：正常，1：禁用',
  `pwd` varchar(255) NOT NULL COMMENT '密码'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `express`
--

CREATE TABLE IF NOT EXISTS `express` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL COMMENT '快递公司名称',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '统一运费',
  `pid` tinyint(4) DEFAULT '0' COMMENT '父id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递公司信息表';

-- --------------------------------------------------------

--
-- 表的结构 `goods`
--

CREATE TABLE IF NOT EXISTS `goods` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '商品名',
  `code` char(150) NOT NULL DEFAULT '' COMMENT '货号',
  `goodsNo` varchar(150) DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '售价',
  `platfPrice` decimal(10,2) DEFAULT '0.00' COMMENT '平台价格',
  `purchPrice` decimal(10,2) DEFAULT '0.00' COMMENT '进货价',
  `attribute` text COMMENT '商品属性',
  `createTime` int(11) DEFAULT '0' COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1:上架，2：售罄，3：停售，4：其他，5：删除',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `fromWhere` tinyint(4) NOT NULL DEFAULT '2' COMMENT '商品来源,1:商家上传,2:平台上传',
  `uploader` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传者',
  `editor` varchar(32) DEFAULT NULL COMMENT '记录编辑者',
  `recommend` tinyint(1) DEFAULT '0' COMMENT '是否推荐：1）推荐',
  `intro` text COMMENT '简介',
  `shopId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺id',
  `freight` decimal(10,2) DEFAULT NULL COMMENT '运费',
  `delflg` enum('0','1') DEFAULT '0' COMMENT '删除',
  `category1` int(11) DEFAULT '0' COMMENT '一级类目',
  `category2` int(11) DEFAULT '0' COMMENT '二级类目',
  `category3` int(11) DEFAULT '0' COMMENT '三级类目',
  `orderTime` int(11) unsigned DEFAULT '0' COMMENT '订单下单时间',
  `content` text COMMENT '商品详情',
  `showPrice` enum('0','1') DEFAULT '1' COMMENT '是否显示价格1显示0不显示',
  `groups` varchar(150) DEFAULT NULL COMMENT '分组',
  `platform` enum('1','2') NOT NULL COMMENT '平台(1供货商,2是平台)',
  `checkResult` tinyint(2) unsigned NOT NULL COMMENT '审核0未审核1审核通过2无文案通过',
  `goodsStock` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '库存',
  `cost` int(11) DEFAULT '0',
  `isChannel` tinyint(4) DEFAULT '0' COMMENT '01',
  `tmp` int(11) DEFAULT NULL COMMENT '临时存放字段',
  `former_platform` tinyint(4) DEFAULT '0' COMMENT '改变前的platform'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品';

-- --------------------------------------------------------

--
-- 表的结构 `goods_check`
--

CREATE TABLE IF NOT EXISTS `goods_check` (
  `id` int(10) unsigned NOT NULL,
  `goodsId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `createTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `oneTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '第一次审核时间',
  `twoTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '第二次审核时间',
  `status` tinyint(4) unsigned NOT NULL COMMENT '审核状态0未审核1一审通过2二审通过',
  `reason` varchar(255) DEFAULT '' COMMENT '审核失败原因',
  `fromWhere` tinyint(4) NOT NULL DEFAULT '2' COMMENT '审核来源,1:商家商品,2:平台商品'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `goods_image`
--

CREATE TABLE IF NOT EXISTS `goods_image` (
  `id` int(11) NOT NULL,
  `goodsId` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `imgurl` varchar(150) DEFAULT NULL COMMENT '图片名',
  `sort` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商品图片';

-- --------------------------------------------------------

--
-- 表的结构 `goods_image_push`
--

CREATE TABLE IF NOT EXISTS `goods_image_push` (
  `id` int(10) unsigned NOT NULL COMMENT '自身ID',
  `goodsId` int(10) unsigned NOT NULL COMMENT '商品ID',
  `imgUrl` varchar(128) NOT NULL DEFAULT '' COMMENT '商品图片路径',
  `channel` int(10) unsigned NOT NULL COMMENT '渠道ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `goods_price_history`
--

CREATE TABLE IF NOT EXISTS `goods_price_history` (
  `id` int(11) unsigned NOT NULL,
  `purchPrice` decimal(10,2) unsigned DEFAULT NULL COMMENT '进货价',
  `updateTime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `goodsId` int(10) DEFAULT NULL,
  `devId` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `goods_templet`
--

CREATE TABLE IF NOT EXISTS `goods_templet` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `main_title` varchar(255) DEFAULT NULL COMMENT '主标题',
  `small_title` varchar(255) DEFAULT NULL COMMENT '副标题',
  `cat_id` int(10) unsigned NOT NULL COMMENT '类目id',
  `is_img` enum('0','1') DEFAULT NULL COMMENT '0：引入商品图片，1：不引入商品图片',
  `is_spec` enum('0','1') DEFAULT NULL COMMENT '0：引入商品规格，1：不引入商品规格',
  `info` text COMMENT '描述',
  `createtime` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `edittime` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  `creator` char(11) DEFAULT NULL COMMENT '创建者',
  `editor` char(11) DEFAULT NULL COMMENT '更新者',
  `sort` int(10) unsigned DEFAULT NULL COMMENT '排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `goods_templet_image`
--

CREATE TABLE IF NOT EXISTS `goods_templet_image` (
  `id` int(10) NOT NULL,
  `temp_id` int(10) unsigned DEFAULT NULL COMMENT '模板表id',
  `image` varchar(255) DEFAULT NULL COMMENT '模板图片名称',
  `sort` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `con` varchar(50) NOT NULL,
  `act` varchar(50) NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜单表';

-- --------------------------------------------------------

--
-- 表的结构 `node`
--

CREATE TABLE IF NOT EXISTS `node` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '节点名称',
  `con` varchar(50) DEFAULT NULL COMMENT '控制器名称',
  `act` varchar(50) DEFAULT NULL COMMENT '方法名称',
  `pid` int(11) NOT NULL COMMENT '父菜单id',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `level` int(11) DEFAULT NULL COMMENT '等级',
  `isMenu` int(11) DEFAULT NULL COMMENT '是否为菜单：1是，0否',
  `status` int(11) DEFAULT NULL COMMENT '状态:1启用，0禁用'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='节点表';

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) unsigned NOT NULL,
  `orderCode` varchar(150) NOT NULL COMMENT '订单编号',
  `goodsId` int(11) unsigned NOT NULL,
  `shopId` int(11) NOT NULL COMMENT '店铺Id',
  `price` decimal(8,2) NOT NULL,
  `isDeliver` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '是否发货1未发2已发',
  `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户',
  `uname` varchar(150) NOT NULL COMMENT '收货人姓名',
  `tel` char(11) NOT NULL DEFAULT '0' COMMENT '收货人手机号',
  `province` int(11) NOT NULL DEFAULT '0' COMMENT '省',
  `city` int(11) NOT NULL DEFAULT '0' COMMENT '市',
  `area` int(11) NOT NULL DEFAULT '0' COMMENT '区',
  `address` varchar(255) NOT NULL COMMENT '收货人地址',
  `createTime` int(11) NOT NULL DEFAULT '0' COMMENT '创建订单时间',
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `isPay` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否付款0未付款，1付款',
  `payWay` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT '支付方式1是微信2是支付宝3是渠道',
  `isDel` enum('0','1') NOT NULL DEFAULT '0' COMMENT '取消订单',
  `deliverWay` enum('1','2') NOT NULL DEFAULT '1' COMMENT '送货方式1快递2自取',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '订单备注',
  `isTake` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '是否收货0默认值1未收2已收',
  `channel` int(11) NOT NULL DEFAULT '0' COMMENT '渠道',
  `payTime` int(11) NOT NULL COMMENT '支付时间',
  `orderType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '订单类型1商家2平台3分销',
  `freight` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `reback` tinyint(2) NOT NULL DEFAULT '0' COMMENT '退货',
  `payw` int(11) NOT NULL COMMENT '付款方式',
  `payType` int(11) NOT NULL COMMENT '付款类型',
  `payBank` int(11) NOT NULL COMMENT '卡',
  `sellerremark` varchar(150) DEFAULT NULL COMMENT '卖家备注',
  `telephone` varchar(11) DEFAULT '0' COMMENT '座机电话',
  `number` varchar(11) DEFAULT '1' COMMENT '数量',
  `sellType` tinyint(4) DEFAULT '0' COMMENT '售出方式：0后台正常订单，1供货商app，2供货商h5，3后台人工售出，4分销商app，5分销商h5',
  `price_certificate` decimal(10,2) DEFAULT '0.00',
  `price_pack` decimal(10,2) DEFAULT '0.00',
  `price_mount` decimal(10,2) DEFAULT '0.00',
  `price_other` decimal(10,2) DEFAULT '0.00',
  `accountId` int(11) DEFAULT '0' COMMENT '结算表Id',
  `account_status` tinyint(4) DEFAULT '0' COMMENT '财务审核状态：0 未提交，1已提交 ，2审核通过，3驳回',
  `operator` varchar(50) DEFAULT '' COMMENT '下单人',
  `isDelete` tinyint(4) DEFAULT '0' COMMENT '是否删除：0 否，1是',
  `isBrush` tinyint(4) DEFAULT '0' COMMENT '是否刷单'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单';

-- --------------------------------------------------------

--
-- 表的结构 `orders_express`
--

CREATE TABLE IF NOT EXISTS `orders_express` (
  `id` int(10) unsigned NOT NULL,
  `expressId` int(11) NOT NULL COMMENT '快递公司',
  `number` char(50) NOT NULL COMMENT '快递单号',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发货时间',
  `orderId` int(11) NOT NULL COMMENT '订单id',
  `real_freight` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `orders_price`
--

CREATE TABLE IF NOT EXISTS `orders_price` (
  `id` int(11) unsigned NOT NULL,
  `orderId` int(11) NOT NULL COMMENT '订单id',
  `price_freight` decimal(10,2) DEFAULT '0.00' COMMENT '统一运费',
  `real_freight` decimal(10,2) DEFAULT '0.00' COMMENT '实付运费',
  `price_certificate` decimal(10,2) DEFAULT '0.00' COMMENT '证书费用',
  `real_certificate` decimal(10,2) DEFAULT '0.00' COMMENT '实付证书费用',
  `price_pack` decimal(10,2) DEFAULT '0.00' COMMENT '包装费用',
  `real_pack` decimal(10,2) DEFAULT '0.00' COMMENT '实付包装费用',
  `price_mount` decimal(10,2) DEFAULT '0.00' COMMENT '装裱费用',
  `real_mount` decimal(10,2) DEFAULT '0.00' COMMENT '实付装裱费用',
  `price_other` decimal(10,2) DEFAULT '0.00' COMMENT '其他费用',
  `real_other` decimal(10,2) DEFAULT '0.00' COMMENT '实付其他费用',
  `updateTime` int(11) DEFAULT '0' COMMENT '修改时间',
  `price_fluctuate` decimal(10,2) DEFAULT '0.00' COMMENT '价格波动',
  `price_platf` decimal(10,2) DEFAULT '0.00' COMMENT '平台费',
  `price_transfer` decimal(10,2) DEFAULT '0.00' COMMENT '转账费'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `parameter`
--

CREATE TABLE IF NOT EXISTS `parameter` (
  `id` int(11) unsigned NOT NULL,
  `cid` int(11) unsigned NOT NULL,
  `name` char(150) DEFAULT NULL COMMENT '参数键',
  `pid` int(11) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='参数';

-- --------------------------------------------------------

--
-- 表的结构 `pay_bank`
--

CREATE TABLE IF NOT EXISTS `pay_bank` (
  `id` int(10) unsigned NOT NULL,
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '付款类型id',
  `bid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '银行卡id',
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `pay_type`
--

CREATE TABLE IF NOT EXISTS `pay_type` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(2) unsigned NOT NULL COMMENT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `pay_way`
--

CREATE TABLE IF NOT EXISTS `pay_way` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `presell`
--

CREATE TABLE IF NOT EXISTS `presell` (
  `id` int(10) unsigned NOT NULL,
  `pushId` int(11) NOT NULL DEFAULT '0' COMMENT '推送商品Id',
  `preTime` char(50) NOT NULL COMMENT '预售时间',
  `startTime` char(50) NOT NULL COMMENT '锁定开始时间',
  `endTime` char(50) NOT NULL COMMENT '锁定结束时间',
  `sellType` char(150) NOT NULL COMMENT '销售方式'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `push`
--

CREATE TABLE IF NOT EXISTS `push` (
  `id` int(11) unsigned NOT NULL,
  `goodsId` int(11) NOT NULL COMMENT '商品',
  `createTime` int(11) NOT NULL COMMENT '添加时间',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态0正常，1是审核中，2是锁定，3价格异常',
  `channel` int(11) NOT NULL DEFAULT '1' COMMENT '出售渠道1api,2自售',
  `devId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推送者id',
  `fromWhere` tinyint(4) NOT NULL DEFAULT '2' COMMENT '推送来源,1:商家商品,2:平台商品',
  `cGoodsId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '该商品对应渠道的商品ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '角色名称',
  `pid` int(11) NOT NULL COMMENT '父角色id',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `status` int(11) DEFAULT NULL COMMENT '状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色表';

-- --------------------------------------------------------

--
-- 表的结构 `role_node`
--

CREATE TABLE IF NOT EXISTS `role_node` (
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `node_id` int(11) NOT NULL COMMENT '节点id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色-节点表';

-- --------------------------------------------------------

--
-- 表的结构 `sell_plan`
--

CREATE TABLE IF NOT EXISTS `sell_plan` (
  `id` int(11) NOT NULL,
  `startTime` int(11) NOT NULL DEFAULT '0' COMMENT '预售计划开始时间',
  `endTime` int(11) NOT NULL DEFAULT '0' COMMENT '预售计划结束时间',
  `addPrice` decimal(10,2) DEFAULT '0.00' COMMENT '附加费用',
  `tradeStyle` tinyint(1) unsigned NOT NULL COMMENT '交易方式。0为竞买，1为一口价。',
  `tradePrice` decimal(10,2) DEFAULT '0.00' COMMENT '单件交易价格。若为竞买模式，则为起拍价',
  `tradeCount` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '交易数量。当为竞买模式，为1。',
  `channelPrice` decimal(10,2) DEFAULT '0.00' COMMENT '渠道价格',
  `express` int(11) unsigned NOT NULL DEFAULT '5' COMMENT '快递',
  `package` int(11) unsigned NOT NULL DEFAULT '12' COMMENT '包装费用',
  `certificate` int(11) unsigned NOT NULL DEFAULT '10' COMMENT '证书费用（默认10（普通证书））',
  `startUpTime` int(11) NOT NULL DEFAULT '0' COMMENT '开始上架时间',
  `goodsId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `shop`
--

CREATE TABLE IF NOT EXISTS `shop` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(50) DEFAULT '' COMMENT '店铺名称',
  `province` int(10) unsigned DEFAULT '0' COMMENT '省份',
  `city` int(10) unsigned DEFAULT '0' COMMENT '城市',
  `area` int(10) unsigned DEFAULT '0' COMMENT '地区',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `type` enum('1','3') NOT NULL COMMENT '类型1供应商2分销商',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '店铺状态',
  `scode` char(150) DEFAULT NULL COMMENT '店铺编号',
  `ptimes` decimal(10,2) NOT NULL COMMENT '平倍',
  `mtimes` decimal(10,2) NOT NULL COMMENT '市倍',
  `headimgurl` varchar(200) DEFAULT NULL COMMENT '店铺头像',
  `intro` text COMMENT '店铺描述',
  `num_day` int(11) DEFAULT '0',
  `category` tinyint(4) DEFAULT NULL COMMENT '分组类别：1只有一个，2绣花张，3悦榕阁',
  `quota` int(11) DEFAULT '500' COMMENT '包邮限额',
  `principal` varchar(50) DEFAULT '' COMMENT '负责人',
  `period` int(11) DEFAULT '7' COMMENT '结算周期'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='店铺';

-- --------------------------------------------------------

--
-- 表的结构 `shopkeeper`
--

CREATE TABLE IF NOT EXISTS `shopkeeper` (
  `id` int(10) unsigned NOT NULL COMMENT 'id',
  `uid` int(10) unsigned NOT NULL COMMENT '店主id',
  `sid` int(10) unsigned NOT NULL COMMENT '店铺id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺管理者';

-- --------------------------------------------------------

--
-- 表的结构 `shop_push`
--

CREATE TABLE IF NOT EXISTS `shop_push` (
  `id` int(10) unsigned NOT NULL COMMENT '自身ID',
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `push_time` int(10) unsigned NOT NULL COMMENT '推送时间',
  `command_id` int(10) unsigned DEFAULT NULL COMMENT '指派人',
  `command_time` int(10) unsigned DEFAULT NULL COMMENT '指派时间',
  `exe_id` int(10) unsigned DEFAULT NULL COMMENT '执行人',
  `s_time` int(10) unsigned DEFAULT NULL COMMENT '图片开始时间',
  `e_time` int(10) unsigned DEFAULT NULL COMMENT '图片结束时间',
  `follow` tinyint(3) unsigned DEFAULT NULL COMMENT '随后的状态,1:文案丰富,2:等待上架'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL COMMENT '电话',
  `createTime` int(11) DEFAULT NULL COMMENT '创建时间',
  `account` varchar(40) DEFAULT NULL,
  `pwd` char(32) DEFAULT NULL,
  `avata` varchar(150) DEFAULT NULL COMMENT '头像',
  `role` enum('0','1','2') DEFAULT '0' COMMENT '0:普通用户，1：商户，2：管理员',
  `status` enum('0','1') DEFAULT '0' COMMENT '0：正常，1：禁用',
  `sex` enum('1','0') DEFAULT '1' COMMENT '性别1男0女',
  `weixin` char(150) DEFAULT NULL COMMENT '微信号',
  `info` varchar(255) DEFAULT NULL COMMENT '个人说明',
  `province` int(10) unsigned DEFAULT NULL COMMENT '省',
  `city` int(10) unsigned DEFAULT NULL COMMENT '市',
  `area` int(10) unsigned DEFAULT NULL COMMENT '区',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `shopId` int(10) unsigned DEFAULT NULL COMMENT '默认店铺'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家用户';

-- --------------------------------------------------------

--
-- 表的结构 `user_action_log`
--

CREATE TABLE IF NOT EXISTS `user_action_log` (
  `id` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `gid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `name` varchar(150) NOT NULL COMMENT '操作',
  `controller` varchar(150) NOT NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(150) NOT NULL DEFAULT '' COMMENT '动作',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间',
  `devUid` int(10) unsigned NOT NULL COMMENT '后台操作人员',
  `shop` int(10) unsigned NOT NULL COMMENT '店铺',
  `realPrice` varchar(11) DEFAULT '0',
  `number` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `role_id` int(11) NOT NULL COMMENT '角色id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户-角色表';

-- --------------------------------------------------------

--
-- 替换视图以便查看 `view_goods_1ge`
--
CREATE TABLE IF NOT EXISTS `view_goods_1ge` (
`id` int(11) unsigned
,`name` varchar(255)
,`purchPrice` decimal(10,2)
,`attribute` text
,`category1` int(11)
,`category2` int(11)
,`category3` int(11)
,`intro` text
,`shopId` int(11) unsigned
,`channel` int(11)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `view_goods_xiuhua`
--
CREATE TABLE IF NOT EXISTS `view_goods_xiuhua` (
`id` int(11) unsigned
,`name` varchar(255)
,`purchPrice` decimal(10,2)
,`attribute` text
,`category1` int(11)
,`category2` int(11)
,`category3` int(11)
,`intro` text
,`shopId` int(11) unsigned
,`channel` int(11)
);

-- --------------------------------------------------------

--
-- 视图结构 `view_goods_1ge`
--
DROP TABLE IF EXISTS `view_goods_1ge`;

CREATE ALGORITHM=UNDEFINED DEFINER=`erp`@`%` SQL SECURITY DEFINER VIEW `view_goods_1ge` AS select `g`.`id` AS `id`,`g`.`name` AS `name`,`g`.`purchPrice` AS `purchPrice`,`g`.`attribute` AS `attribute`,`g`.`category1` AS `category1`,`g`.`category2` AS `category2`,`g`.`category3` AS `category3`,`g`.`intro` AS `intro`,`g`.`shopId` AS `shopId`,`p`.`channel` AS `channel` from (`goods` `g` join `push` `p`) where ((`g`.`id` = `p`.`goodsId`) and (`g`.`status` = 1) and (`g`.`shopId` <> 1) and (`p`.`status` = 0));

-- --------------------------------------------------------

--
-- 视图结构 `view_goods_xiuhua`
--
DROP TABLE IF EXISTS `view_goods_xiuhua`;

CREATE ALGORITHM=UNDEFINED DEFINER=`erp`@`%` SQL SECURITY DEFINER VIEW `view_goods_xiuhua` AS select `g`.`id` AS `id`,`g`.`name` AS `name`,`g`.`purchPrice` AS `purchPrice`,`g`.`attribute` AS `attribute`,`g`.`category1` AS `category1`,`g`.`category2` AS `category2`,`g`.`category3` AS `category3`,`g`.`intro` AS `intro`,`g`.`shopId` AS `shopId`,`p`.`channel` AS `channel` from (`goods` `g` join `push` `p`) where ((`g`.`id` = `p`.`goodsId`) and (`g`.`status` = 1) and (`g`.`shopId` = 1) and (`p`.`status` = 0));

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_shop`
--
ALTER TABLE `account_shop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `apimss`
--
ALTER TABLE `apimss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_version`
--
ALTER TABLE `app_version`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attribute`
--
ALTER TABLE `attribute`
  ADD PRIMARY KEY (`attr_id`),
  ADD KEY `parent_pid` (`parent_pid`),
  ADD KEY `parent_vid` (`parent_vid`);

--
-- Indexes for table `attr_relation`
--
ALTER TABLE `attr_relation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_pid` (`parent_pid`),
  ADD KEY `parent_vid` (`parent_vid`);

--
-- Indexes for table `attr_value`
--
ALTER TABLE `attr_value`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attr_pid` (`attr_pid`);

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`);

--
-- Indexes for table `channel`
--
ALTER TABLE `channel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_shop_ctimes`
--
ALTER TABLE `channel_shop_ctimes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_view_log`
--
ALTER TABLE `channel_view_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jid` (`jid`);

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `parent_id_sort_num` (`parent_id`,`sort_num`);

--
-- Indexes for table `copywriter_templet`
--
ALTER TABLE `copywriter_templet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catid` (`cat_id`);

--
-- Indexes for table `cost`
--
ALTER TABLE `cost`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`account`);

--
-- Indexes for table `distributor`
--
ALTER TABLE `distributor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `express`
--
ALTER TABLE `express`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `goods`
--
ALTER TABLE `goods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `name` (`name`),
  ADD KEY `code` (`code`),
  ADD KEY `goodsNo` (`goodsNo`),
  ADD KEY `isChannel` (`isChannel`),
  ADD KEY `checkResult` (`checkResult`),
  ADD KEY `platform` (`platform`),
  ADD KEY `recommend` (`recommend`),
  ADD KEY `shopId` (`shopId`);

--
-- Indexes for table `goods_check`
--
ALTER TABLE `goods_check`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goodsId` (`goodsId`);

--
-- Indexes for table `goods_image`
--
ALTER TABLE `goods_image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `goods_image_push`
--
ALTER TABLE `goods_image_push`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goodsId` (`goodsId`);

--
-- Indexes for table `goods_price_history`
--
ALTER TABLE `goods_price_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goodsId` (`goodsId`) USING BTREE;

--
-- Indexes for table `goods_templet`
--
ALTER TABLE `goods_templet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `goods_templet_image`
--
ALTER TABLE `goods_templet_image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `node`
--
ALTER TABLE `node`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `商品` (`goodsId`),
  ADD KEY `用户` (`userId`);

--
-- Indexes for table `orders_express`
--
ALTER TABLE `orders_express`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders_price`
--
ALTER TABLE `orders_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parameter`
--
ALTER TABLE `parameter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pay_bank`
--
ALTER TABLE `pay_bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pay_type`
--
ALTER TABLE `pay_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pay_way`
--
ALTER TABLE `pay_way`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presell`
--
ALTER TABLE `presell`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push`
--
ALTER TABLE `push`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goodsId` (`goodsId`,`channel`),
  ADD KEY `status` (`status`),
  ADD KEY `channel` (`channel`),
  ADD KEY `cGoodsId` (`cGoodsId`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_node`
--
ALTER TABLE `role_node`
  ADD KEY `role_id` (`role_id`),
  ADD KEY `node_id` (`node_id`);

--
-- Indexes for table `sell_plan`
--
ALTER TABLE `sell_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop`
--
ALTER TABLE `shop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shopkeeper`
--
ALTER TABLE `shopkeeper`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop_push`
--
ALTER TABLE `shop_push`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_action_log`
--
ALTER TABLE `user_action_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD KEY `role_id` (`role_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_shop`
--
ALTER TABLE `account_shop`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `apimss`
--
ALTER TABLE `apimss`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id';
--
-- AUTO_INCREMENT for table `app_version`
--
ALTER TABLE `app_version`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `attribute`
--
ALTER TABLE `attribute`
  MODIFY `attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '属性ID';
--
-- AUTO_INCREMENT for table `attr_relation`
--
ALTER TABLE `attr_relation`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自身ID';
--
-- AUTO_INCREMENT for table `attr_value`
--
ALTER TABLE `attr_value`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自身ID';
--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品类别ID';
--
-- AUTO_INCREMENT for table `channel`
--
ALTER TABLE `channel`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `channel_shop_ctimes`
--
ALTER TABLE `channel_shop_ctimes`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `channel_view_log`
--
ALTER TABLE `channel_view_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id';
--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '地址ID';
--
-- AUTO_INCREMENT for table `copywriter_templet`
--
ALTER TABLE `copywriter_templet`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cost`
--
ALTER TABLE `cost`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `developers`
--
ALTER TABLE `developers`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'user id';
--
-- AUTO_INCREMENT for table `distributor`
--
ALTER TABLE `distributor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `express`
--
ALTER TABLE `express`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `goods`
--
ALTER TABLE `goods`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `goods_check`
--
ALTER TABLE `goods_check`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `goods_image`
--
ALTER TABLE `goods_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `goods_image_push`
--
ALTER TABLE `goods_image_push`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自身ID';
--
-- AUTO_INCREMENT for table `goods_price_history`
--
ALTER TABLE `goods_price_history`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `goods_templet`
--
ALTER TABLE `goods_templet`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `goods_templet_image`
--
ALTER TABLE `goods_templet_image`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node`
--
ALTER TABLE `node`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `orders_express`
--
ALTER TABLE `orders_express`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `orders_price`
--
ALTER TABLE `orders_price`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `parameter`
--
ALTER TABLE `parameter`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pay_bank`
--
ALTER TABLE `pay_bank`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pay_type`
--
ALTER TABLE `pay_type`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pay_way`
--
ALTER TABLE `pay_way`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `presell`
--
ALTER TABLE `presell`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `push`
--
ALTER TABLE `push`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sell_plan`
--
ALTER TABLE `sell_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shop`
--
ALTER TABLE `shop`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `shopkeeper`
--
ALTER TABLE `shopkeeper`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id';
--
-- AUTO_INCREMENT for table `shop_push`
--
ALTER TABLE `shop_push`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自身ID';
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_action_log`
--
ALTER TABLE `user_action_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
