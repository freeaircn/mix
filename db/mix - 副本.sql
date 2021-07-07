-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1:3306
-- 生成日期： 2021-06-27 11:29:12
-- 服务器版本： 10.3.14-MariaDB
-- PHP 版本： 7.3.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `binglang`
--

-- --------------------------------------------------------

--
-- 表的结构 `app_dept`
--

DROP TABLE IF EXISTS `app_dept`;
CREATE TABLE IF NOT EXISTS `app_dept` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `label` varchar(63) NOT NULL COMMENT '名称',
  `pid` int(11) UNSIGNED NOT NULL COMMENT '上级节点',
  `enabled` bit(1) NOT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='部门' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_dept`
--

INSERT INTO `app_dept` (`id`, `sort`, `label`, `pid`, `enabled`, `update_time`) VALUES
(1, 1, '工作室', 0, b'1', '2020-01-01 09:14:05'),
(2, 1, '开发组', 1, b'1', '2020-02-06 22:17:00'),
(3, 2, '测试组', 1, b'1', '2020-02-06 22:17:05'),
(7, 1, '测试一组', 3, b'1', '2020-02-09 22:31:19'),
(8, 2, '开发二组', 2, b'1', '2020-02-06 22:17:21'),
(13, 3, '后勤组', 1, b'1', '2020-02-06 22:17:11'),
(15, 1, '后勤一组', 13, b'1', '2020-02-06 22:05:56'),
(17, 1, '开发一组', 2, b'1', '2020-02-06 22:17:16'),
(18, 2, '后勤二组', 13, b'1', '2020-02-06 22:05:52');

-- --------------------------------------------------------

--
-- 表的结构 `app_dict`
--

DROP TABLE IF EXISTS `app_dict`;
CREATE TABLE IF NOT EXISTS `app_dict` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `label` varchar(20) NOT NULL COMMENT '类型名',
  `name` varchar(63) NOT NULL COMMENT '键名',
  `enabled` bit(1) NOT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uc_dict_label` (`label`) USING BTREE,
  UNIQUE KEY `uc_dict_name` (`name`) USING BTREE,
  KEY `key_dict_sort` (`sort`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='数据词典' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_dict`
--

INSERT INTO `app_dict` (`id`, `sort`, `label`, `name`, `enabled`, `update_time`) VALUES
(5, 1, '用户属性过滤', 'user_prop_mask', b'1', '2021-05-28 03:32:30'),
(11, 2, '厂站名', 'station_name', b'1', '2021-05-28 03:37:22'),
(13, 3, '故障等级', 'fault_level', b'1', '2021-05-28 07:04:55'),
(14, 4, '设备类别一', 'device_category_a', b'1', '2021-05-28 13:03:07'),
(15, 5, '设备类别二', 'device_category_b', b'1', '2021-05-28 13:03:31'),
(16, 5, '设备类别三', 'device_category_c', b'1', '2021-05-28 13:03:56');

-- --------------------------------------------------------

--
-- 表的结构 `app_dict_data`
--

DROP TABLE IF EXISTS `app_dict_data`;
CREATE TABLE IF NOT EXISTS `app_dict_data` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `label` varchar(31) NOT NULL COMMENT '词条名',
  `name` varchar(255) NOT NULL COMMENT '键名',
  `code` int(11) UNSIGNED DEFAULT NULL COMMENT '键值',
  `enabled` bit(1) NOT NULL,
  `dict_id` int(11) UNSIGNED NOT NULL COMMENT '所属字典id',
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uc_dict_data_name` (`name`) USING BTREE,
  KEY `key_dict_data_sort` (`sort`) USING BTREE,
  KEY `fk_dict_data_ref_dict_id` (`dict_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8 COMMENT='数据词条' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_dict_data`
--

INSERT INTO `app_dict_data` (`id`, `sort`, `label`, `name`, `code`, `enabled`, `dict_id`, `update_time`) VALUES
(8, 3, '工号', 'sort', 1, b'1', 5, '2021-01-17 00:02:12'),
(9, 4, '用户名', 'username', 3, b'1', 5, '2021-01-17 00:03:00'),
(10, 5, '性别', 'sex', 3, b'1', 5, '2021-01-17 00:03:50'),
(11, 6, '证件号', 'identity_document_number', 3, b'1', 5, '2021-01-17 00:05:47'),
(12, 7, '手机号', 'phone', 1, b'1', 5, '2021-01-17 00:06:05'),
(13, 8, '邮箱', 'email', 1, b'1', 5, '2021-01-17 00:06:27'),
(14, 9, '启用', 'enabled', 0, b'1', 5, '2021-01-17 00:07:54'),
(15, 10, '要求修改密码', 'force_chg_pwd', 0, b'1', 5, '2021-01-17 00:07:46'),
(16, 11, '部门', 'attr_01_id', 3, b'1', 5, '2021-01-17 00:14:42'),
(17, 12, '岗位', 'attr_02_id', 3, b'1', 5, '2021-01-17 00:15:19'),
(78, 13, '政治面貌', 'attr_03_id', 3, b'1', 5, '2021-01-17 00:15:19'),
(79, 14, '职称', 'attr_04_id', 3, b'1', 5, '2021-01-17 00:15:19'),
(80, 15, '预留', 'attr_05_id', 0, b'1', 5, '2021-01-17 20:32:56'),
(81, 16, '预留', 'attr_06_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(82, 17, '预留', 'attr_07_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(83, 18, '预留', 'attr_08_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(84, 19, '预留', 'attr_09_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(85, 20, '预留', 'attr_10_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(86, 21, '预留', 'attr_11_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(87, 22, '预留', 'attr_12_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(88, 23, '预留', 'attr_13_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(89, 24, '预留', 'attr_14_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(90, 25, '预留', 'attr_15_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(91, 26, '预留', 'attr_16_id', 0, b'1', 5, '2021-01-17 00:15:19'),
(92, 27, '预留', 'attr_text_01', 0, b'1', 5, '2021-01-17 00:15:19'),
(93, 28, '预留', 'attr_text_02', 0, b'1', 5, '2021-01-17 00:15:19'),
(94, 29, '预留', 'attr_text_03', 0, b'1', 5, '2021-01-17 00:15:19'),
(95, 30, '预留', 'attr_text_04', 0, b'1', 5, '2021-01-17 00:15:19'),
(96, 31, '预留', 'attr_text_05', 0, b'1', 5, '2021-01-17 00:15:19'),
(97, 32, '预留', 'attr_text_06', 0, b'1', 5, '2021-01-17 00:15:19'),
(98, 33, '预留', 'attr_text_07', 0, b'1', 5, '2021-01-17 00:15:19'),
(99, 34, '预留', 'attr_text_08', 0, b'1', 5, '2021-01-17 00:15:19'),
(100, 35, '最近登录时间', 'last_login', 1, b'1', 5, '2021-01-17 00:15:19'),
(101, 36, '登录IP', 'ip_address', 0, b'1', 5, '2021-01-17 00:15:19'),
(102, 37, '更改时间', 'update_time', 0, b'1', 5, '2021-01-17 00:15:19'),
(103, 38, '头像ID', 'avatar_id', 1, b'1', 5, '2021-01-17 00:15:19'),
(104, 39, '密码', 'password', 0, b'1', 5, '2021-01-17 00:15:19'),
(105, 40, '忘记密码selector', 'forgotten_password_selector', 0, b'1', 5, '2021-01-17 00:15:19'),
(106, 41, '忘记密码code', 'forgotten_password_code', 0, b'1', 5, '2021-01-17 00:15:19'),
(107, 42, '忘记密码时间', 'forgotten_password_time', 0, b'1', 5, '2021-01-17 00:15:19'),
(108, 1, '松山河口电厂', 'sshkdc', 1, b'1', 11, '2021-05-28 03:36:29'),
(109, 2, '苏家河口电厂', 'sjhkdc', 2, b'1', 11, '2021-05-28 03:37:54'),
(111, 1, '一级', 'critical', 1, b'1', 13, '2021-05-28 03:44:06'),
(112, 2, '二级', 'major', 2, b'1', 13, '2021-05-28 03:44:45'),
(113, 1, '一号机组', 'generator_a', 101, b'1', 14, '2021-05-28 13:08:30'),
(114, 1, '二号机组', 'generator_b', 102, b'1', 14, '2021-05-28 13:13:37'),
(115, 1, '三号机组', 'generator_c', 103, b'1', 14, '2021-05-28 13:14:33'),
(116, 1, '公用设备', 'public_device', 104, b'1', 14, '2021-05-28 13:16:20'),
(117, 1, '调速器', 'speed_controller', 201, b'1', 15, '2021-05-28 13:18:31');

-- --------------------------------------------------------

--
-- 表的结构 `app_job`
--

DROP TABLE IF EXISTS `app_job`;
CREATE TABLE IF NOT EXISTS `app_job` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `label` varchar(31) NOT NULL COMMENT '中文名称',
  `enabled` bit(1) NOT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='岗位' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_job`
--

INSERT INTO `app_job` (`id`, `sort`, `label`, `enabled`, `update_time`) VALUES
(1, 1, '开发员', b'1', '2020-01-01 09:14:05'),
(2, 2, '测试员', b'1', '2020-01-01 09:14:05'),
(3, 3, '保洁员', b'1', '2020-02-07 20:43:28'),
(4, 5, '采购员', b'1', '2020-02-07 20:45:52'),
(5, 4, '销售员', b'1', '2020-02-07 20:47:43');

-- --------------------------------------------------------

--
-- 表的结构 `app_login_attempts`
--

DROP TABLE IF EXISTS `app_login_attempts`;
CREATE TABLE IF NOT EXISTS `app_login_attempts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `identity` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `app_login_attempts`
--

INSERT INTO `app_login_attempts` (`id`, `ip_address`, `identity`, `time`) VALUES
(10, '127.0.0.1', '13812346578', 1602936181),
(11, '127.0.0.1', '13812346578', 1602936212),
(12, '127.0.0.1', '13812346578', 1602936217),
(13, '127.0.0.1', '13712345677', 1605351590),
(14, '127.0.0.1', '13821345678', 1605603540),
(15, '127.0.0.1', '13812346678', 1610362507),
(16, '127.0.0.1', '13813246578', 1610552956),
(17, '127.0.0.1', '13812346578', 1610553222),
(21, '127.0.0.1', '13813245678', 1611576970),
(23, '127.0.0.1', '13812346578', 1611577379);

-- --------------------------------------------------------

--
-- 表的结构 `app_menu`
--

DROP TABLE IF EXISTS `app_menu`;
CREATE TABLE IF NOT EXISTS `app_menu` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` int(11) UNSIGNED NOT NULL COMMENT '菜单-1，按钮-2',
  `name` varchar(63) DEFAULT NULL COMMENT '路由/组件名称',
  `path` varchar(63) DEFAULT NULL COMMENT '路由Path',
  `component` varchar(127) DEFAULT NULL COMMENT '组件懒加载',
  `redirect` varchar(127) DEFAULT NULL COMMENT '重定向',
  `hidden` bit(1) NOT NULL DEFAULT b'0' COMMENT '侧边栏隐藏',
  `alwaysShow` bit(1) NOT NULL DEFAULT b'0' COMMENT '侧边栏显示顶级目录',
  `title` varchar(31) NOT NULL COMMENT '菜单标题',
  `icon` varchar(63) DEFAULT NULL COMMENT '图标',
  `noCache` bit(1) NOT NULL DEFAULT b'1' COMMENT '页面缓存',
  `breadcrumb` bit(1) NOT NULL DEFAULT b'1' COMMENT '面包屑显示',
  `roles` varchar(255) DEFAULT NULL COMMENT '权限',
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `pid` int(11) UNSIGNED NOT NULL COMMENT '上级菜单ID',
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `key_menu_pid` (`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COMMENT='页面菜单' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_menu`
--

INSERT INTO `app_menu` (`id`, `type`, `name`, `path`, `component`, `redirect`, `hidden`, `alwaysShow`, `title`, `icon`, `noCache`, `breadcrumb`, `roles`, `sort`, `pid`, `update_time`) VALUES
(1, 1, 'Admin', 'admin', 'Layout', 'noRedirect', b'0', b'0', '系统管理', '', b'1', b'1', 'admin:get', 1, 0, '2020-02-08 16:56:48'),
(2, 1, 'AdminDept', 'dept', 'admin/dept/index', '', b'0', b'0', '部门管理', '', b'1', b'1', 'dept:get', 1, 1, '2020-11-14 16:08:35'),
(3, 1, 'AdminJob', 'job', 'admin/job/index', '', b'0', b'0', '岗位管理', '', b'1', b'1', 'job:get', 2, 1, '2020-11-14 16:09:53'),
(4, 1, 'AdminUser', 'user', 'admin/user/index', '', b'0', b'0', '用户管理', '', b'1', b'1', 'user:get', 3, 1, '2020-11-14 16:11:35'),
(5, 1, 'AdminRole', 'role', 'admin/role/index', '', b'0', b'0', '角色管理', '', b'1', b'1', 'role:get', 4, 1, '2020-11-14 16:10:19'),
(6, 1, 'AdminMenu', 'menu', 'admin/menu/index', '', b'0', b'0', '菜单管理', '', b'1', b'1', 'menu:get', 5, 1, '2020-11-14 16:07:59'),
(7, 1, 'AdminDict', 'dict', 'admin/dict/index', '', b'0', b'0', '词典管理', '', b'1', b'1', 'dict:get', 6, 1, '2020-11-14 16:09:06'),
(8, 1, 'AdminDictData', 'dict_data', 'admin/dict/dict-data', '', b'0', b'0', '词条管理', '', b'1', b'1', 'dict_data:get', 7, 1, '2020-11-16 21:52:06'),
(12, 1, 'account', 'account', 'Layout', 'noRedirect', b'0', b'1', '个人信息', '', b'1', b'1', 'account:get', 2, 0, '2021-05-28 01:45:13'),
(14, 1, 'AccountSetting', 'setting', 'account/index', '', b'0', b'0', '个人信息', '', b'1', b'1', 'account:get', 1, 12, '2020-11-16 22:56:58'),
(15, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'dept:post', 1, 2, '2020-11-16 21:21:08'),
(17, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'dept:put', 2, 2, '2020-11-16 21:25:43'),
(18, 2, '', '', '', 'noRedirect', b'0', b'0', '删除', '', b'1', b'1', 'dept:delete', 3, 2, '2020-11-16 21:26:14'),
(19, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'user:post', 1, 4, '2020-11-16 21:27:17'),
(20, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'user:put', 2, 4, '2020-11-16 21:27:57'),
(21, 2, '', '', '', 'noRedirect', b'0', b'0', '删除', '', b'1', b'1', 'user:delete', 3, 4, '2020-11-16 21:28:19'),
(22, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'job:post', 1, 3, '2020-11-16 21:40:43'),
(23, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'job:put', 2, 3, '2020-11-16 21:41:05'),
(24, 2, '', '', '', 'noRedirect', b'0', b'0', '删除', '', b'1', b'1', 'job:delete', 3, 3, '2020-11-16 21:41:33'),
(25, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'role:post', 1, 5, '2020-11-16 21:43:00'),
(26, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'role:put', 2, 5, '2020-11-16 21:43:19'),
(27, 2, '', '', '', 'noRedirect', b'0', b'0', '删除', '', b'1', b'1', 'role:delete', 3, 5, '2020-11-16 21:43:39'),
(28, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'menu:post', 1, 6, '2020-11-16 21:44:09'),
(29, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'menu:put', 2, 6, '2020-11-16 21:44:26'),
(30, 2, '', '', '', 'noRedirect', b'0', b'0', '删除', '', b'1', b'1', 'menu:delete', 3, 6, '2020-11-16 21:44:45'),
(31, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'dict:post', 1, 7, '2020-11-16 21:45:22'),
(32, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'dict:put', 2, 7, '2020-11-16 21:45:45'),
(33, 2, '', '', '', 'noRedirect', b'0', b'0', '删除', '', b'1', b'1', 'dict:delete', 3, 7, '2020-11-16 21:46:03'),
(34, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'account:post', 1, 14, '2020-11-16 21:47:12'),
(35, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'account:put', 2, 14, '2020-11-16 21:47:33'),
(36, 2, '', '', '', 'noRedirect', b'0', b'0', '查询角色菜单', '', b'1', b'1', 'role_menu:get', 4, 5, '2020-11-16 21:56:47'),
(37, 2, '', '', '', 'noRedirect', b'0', b'0', '新增角色菜单', '', b'1', b'1', 'role_menu:post', 5, 5, '2020-11-16 21:56:17'),
(38, 2, '', '', '', 'noRedirect', b'0', b'0', '新增', '', b'1', b'1', 'dict_data:post', 1, 8, '2020-11-16 21:58:41'),
(39, 2, '', '', '', 'noRedirect', b'0', b'0', '更新', '', b'1', b'1', 'dict_data:put', 2, 8, '2020-11-16 22:07:34'),
(40, 2, '', '', '', 'noRedirect', b'0', b'0', '删除', '', b'1', b'1', 'dict_data:delete', 3, 8, '2020-11-16 21:59:23'),
(42, 1, 'Fault', 'fault', 'Layout', 'noRedirect', b'0', b'0', '设备故障', '', b'1', b'1', 'fault:get', 3, 0, '2021-05-28 07:56:10'),
(43, 1, 'FaultIndex', 'index', 'fault/index/index', '', b'0', b'0', '首页', '', b'1', b'1', 'fault:get', 1, 42, '2021-05-28 03:15:43'),
(44, 1, 'FaultCreate', 'create', 'fault/create', '', b'0', b'0', '新建', '', b'1', b'1', 'fault:post', 2, 42, '2021-05-28 03:16:17'),
(54, 1, 'FaultDelete', 'delete', 'fault/delete', '', b'0', b'0', '删除', '', b'1', b'1', 'fault:delete', 3, 42, '2021-05-28 12:45:34');

-- --------------------------------------------------------

--
-- 表的结构 `app_politic`
--

DROP TABLE IF EXISTS `app_politic`;
CREATE TABLE IF NOT EXISTS `app_politic` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `label` varchar(31) NOT NULL COMMENT '中文名称',
  `enabled` bit(1) NOT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='政治面貌' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_politic`
--

INSERT INTO `app_politic` (`id`, `sort`, `label`, `enabled`, `update_time`) VALUES
(1, 1, '群众', b'1', '2020-01-01 09:14:05'),
(2, 2, '中共党员', b'1', '2020-01-01 09:14:05');

-- --------------------------------------------------------

--
-- 表的结构 `app_professional_title`
--

DROP TABLE IF EXISTS `app_professional_title`;
CREATE TABLE IF NOT EXISTS `app_professional_title` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `label` varchar(31) NOT NULL COMMENT '中文名称',
  `enabled` bit(1) NOT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='职称' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_professional_title`
--

INSERT INTO `app_professional_title` (`id`, `sort`, `label`, `enabled`, `update_time`) VALUES
(1, 1, '工程师', b'1', '2020-01-01 09:14:05'),
(2, 2, '助理工程师', b'1', '2020-01-01 09:14:05');

-- --------------------------------------------------------

--
-- 表的结构 `app_role`
--

DROP TABLE IF EXISTS `app_role`;
CREATE TABLE IF NOT EXISTS `app_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `sort` int(11) UNSIGNED DEFAULT NULL COMMENT '排序',
  `label` varchar(31) NOT NULL COMMENT '名称',
  `name` varchar(63) NOT NULL COMMENT '键名',
  `enabled` bit(1) NOT NULL,
  `remark` varchar(127) DEFAULT NULL COMMENT '备注',
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='角色表' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_role`
--

INSERT INTO `app_role` (`id`, `sort`, `label`, `name`, `enabled`, `remark`, `update_time`) VALUES
(1, 1, '管理组', 'admin_group', b'1', '具有管理员权限', '2020-02-07 21:45:26'),
(2, 2, '访客组', 'guest_group', b'1', '具有访客权限', '2020-02-07 21:45:19'),
(3, 3, '开发组', 'develop_group', b'1', '具有开发员的权限', '2020-02-07 21:44:37'),
(4, 4, '测试组', 'test_group', b'1', '具有测试员权限', '2020-02-07 21:46:06');

-- --------------------------------------------------------

--
-- 表的结构 `app_roles_menus`
--

DROP TABLE IF EXISTS `app_roles_menus`;
CREATE TABLE IF NOT EXISTS `app_roles_menus` (
  `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
  `menu_id` int(11) UNSIGNED NOT NULL COMMENT '菜单ID',
  PRIMARY KEY (`role_id`,`menu_id`) USING BTREE,
  KEY `fk_roles_menus_ref_role_id` (`role_id`) USING BTREE,
  KEY `fk_roles_menus_ref_menu_id` (`menu_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色菜单关联' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_roles_menus`
--

INSERT INTO `app_roles_menus` (`role_id`, `menu_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 12),
(1, 14),
(1, 15),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 42),
(1, 43),
(1, 44),
(1, 54),
(3, 1),
(3, 2),
(3, 7),
(3, 8);

-- --------------------------------------------------------

--
-- 表的结构 `app_user`
--

DROP TABLE IF EXISTS `app_user`;
CREATE TABLE IF NOT EXISTS `app_user` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sort` int(11) UNSIGNED NOT NULL COMMENT '工号',
  `username` varchar(10) NOT NULL COMMENT '中文名',
  `sex` varchar(3) NOT NULL COMMENT '1-女，0-男',
  `identity_document_number` varchar(31) DEFAULT NULL COMMENT '证件号',
  `phone` varchar(15) NOT NULL,
  `email` varchar(63) NOT NULL,
  `enabled` varchar(3) NOT NULL,
  `force_chg_pwd` varchar(3) NOT NULL DEFAULT '1' COMMENT '要求修改密码',
  `attr_01_id` int(11) UNSIGNED DEFAULT NULL COMMENT '部门',
  `attr_02_id` int(11) UNSIGNED DEFAULT NULL COMMENT '岗位',
  `attr_03_id` int(11) UNSIGNED DEFAULT NULL COMMENT '政治面貌',
  `attr_04_id` int(11) UNSIGNED DEFAULT NULL COMMENT '职称',
  `attr_05_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_06_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_07_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_08_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_09_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_10_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_11_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_12_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_13_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_14_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_15_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_16_id` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `attr_text_01` varchar(63) DEFAULT NULL COMMENT '预留',
  `attr_text_02` varchar(63) DEFAULT NULL COMMENT '预留',
  `attr_text_03` varchar(63) DEFAULT NULL COMMENT '预留',
  `attr_text_04` varchar(63) DEFAULT NULL COMMENT '预留',
  `attr_text_05` varchar(63) DEFAULT NULL COMMENT '预留',
  `attr_text_06` varchar(63) DEFAULT NULL COMMENT '预留',
  `attr_text_07` varchar(63) DEFAULT NULL COMMENT '预留',
  `attr_text_08` varchar(63) DEFAULT NULL COMMENT '预留',
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(63) DEFAULT NULL,
  `update_time` datetime DEFAULT NULL COMMENT '更新日期',
  `avatar_id` int(11) UNSIGNED DEFAULT NULL COMMENT '头像',
  `password` varchar(255) NOT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uc_user_sort` (`sort`) USING BTREE,
  UNIQUE KEY `uc_phone` (`phone`) USING BTREE,
  UNIQUE KEY `uc_email` (`email`) USING BTREE,
  UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`) USING BTREE,
  KEY `key_username` (`username`) USING BTREE,
  KEY `fk_user_ref_attr_01_id` (`attr_01_id`) USING BTREE,
  KEY `fk_user_ref_attr_02_id` (`attr_02_id`) USING BTREE,
  KEY `fk_user_ref_attr_03_id` (`attr_03_id`) USING BTREE,
  KEY `fk_user_ref_attr_04_id` (`attr_04_id`) USING BTREE,
  KEY `fk_user_ref_attr_05_id` (`attr_05_id`) USING BTREE,
  KEY `fk_user_ref_attr_06_id` (`attr_06_id`) USING BTREE,
  KEY `fk_user_ref_attr_07_id` (`attr_07_id`) USING BTREE,
  KEY `fk_user_ref_attr_08_id` (`attr_08_id`) USING BTREE,
  KEY `fk_user_ref_attr_09_id` (`attr_09_id`) USING BTREE,
  KEY `fk_user_ref_attr_10_id` (`attr_10_id`) USING BTREE,
  KEY `fk_user_ref_attr_11_id` (`attr_11_id`) USING BTREE,
  KEY `fk_user_ref_attr_12_id` (`attr_12_id`) USING BTREE,
  KEY `fk_user_ref_attr_13_id` (`attr_13_id`) USING BTREE,
  KEY `fk_user_ref_attr_14_id` (`attr_14_id`) USING BTREE,
  KEY `fk_user_ref_attr_15_id` (`attr_15_id`) USING BTREE,
  KEY `fk_user_ref_attr_16_id` (`attr_16_id`) USING BTREE,
  KEY `fk_user_ref_avatar_id` (`avatar_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='用户' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user`
--

INSERT INTO `app_user` (`id`, `sort`, `username`, `sex`, `identity_document_number`, `phone`, `email`, `enabled`, `force_chg_pwd`, `attr_01_id`, `attr_02_id`, `attr_03_id`, `attr_04_id`, `attr_05_id`, `attr_06_id`, `attr_07_id`, `attr_08_id`, `attr_09_id`, `attr_10_id`, `attr_11_id`, `attr_12_id`, `attr_13_id`, `attr_14_id`, `attr_15_id`, `attr_16_id`, `attr_text_01`, `attr_text_02`, `attr_text_03`, `attr_text_04`, `attr_text_05`, `attr_text_06`, `attr_text_07`, `attr_text_08`, `last_login`, `ip_address`, `update_time`, `avatar_id`, `password`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`) VALUES
(1, 1, '小明', '0', '', '13812345678', '3@3.3', '1', '0', 8, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1624792999, NULL, '2021-05-03 13:27:37', 1, '$argon2i$v=19$m=16384,t=4,p=2$My9kc2ZXZHUvNkx1ZjFCdA$6ZcgoVANOcxp7g6qyGO5ICw9w94Rhbapk8Nz92zB9bk', NULL, NULL, NULL),
(15, 2, '李白', '0', '', '13812345679', '2@2.2', '1', '1', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1611478321, NULL, '2021-01-17 22:37:40', 8, '$argon2i$v=19$m=16384,t=4,p=2$RElaMTduWlN1NE9iNnc0ag$vRE91WQPpaLxxyNY8tod7j+UlpcBvtemVzU201Duxp8', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `app_users_roles`
--

DROP TABLE IF EXISTS `app_users_roles`;
CREATE TABLE IF NOT EXISTS `app_users_roles` (
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`user_id`,`role_id`) USING BTREE,
  KEY `fk_users_roles_ref_user_id` (`user_id`) USING BTREE,
  KEY `fk_users_roles_ref_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户角色关联' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_users_roles`
--

INSERT INTO `app_users_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 3),
(15, 1);

-- --------------------------------------------------------

--
-- 表的结构 `app_user_avatar`
--

DROP TABLE IF EXISTS `app_user_avatar`;
CREATE TABLE IF NOT EXISTS `app_user_avatar` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `real_name` varchar(255) DEFAULT NULL COMMENT '真实文件名',
  `path` varchar(255) DEFAULT NULL COMMENT '路径',
  `size` varchar(255) DEFAULT NULL COMMENT '大小',
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='用户头像' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user_avatar`
--

INSERT INTO `app_user_avatar` (`id`, `real_name`, `path`, `size`, `update_time`) VALUES
(1, '13812345678_1614223133.jpg', 'resource/avatar/active/', '9.78', '2021-02-25 11:18:53'),
(8, 'avatar_default_male.jpg', '/resource/avatar/default/', NULL, '2021-01-16 23:58:45');

-- --------------------------------------------------------

--
-- 表的结构 `app_verification_code`
--

DROP TABLE IF EXISTS `app_verification_code`;
CREATE TABLE IF NOT EXISTS `app_verification_code` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` varchar(15) NOT NULL,
  `code` varchar(5) DEFAULT NULL,
  `created_on` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uc_verification_code_phone` (`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

--
-- 限制导出的表
--

--
-- 限制表 `app_dict_data`
--
ALTER TABLE `app_dict_data`
  ADD CONSTRAINT `fk_dict_data_ref_dict_id` FOREIGN KEY (`dict_id`) REFERENCES `app_dict` (`id`);

--
-- 限制表 `app_roles_menus`
--
ALTER TABLE `app_roles_menus`
  ADD CONSTRAINT `fk_roles_menus_ref_menu_id` FOREIGN KEY (`menu_id`) REFERENCES `app_menu` (`id`),
  ADD CONSTRAINT `fk_roles_menus_ref_role_id` FOREIGN KEY (`role_id`) REFERENCES `app_role` (`id`);

--
-- 限制表 `app_user`
--
ALTER TABLE `app_user`
  ADD CONSTRAINT `fk_user_ref_attr_01_id` FOREIGN KEY (`attr_01_id`) REFERENCES `app_dept` (`id`),
  ADD CONSTRAINT `fk_user_ref_attr_02_id` FOREIGN KEY (`attr_02_id`) REFERENCES `app_job` (`id`),
  ADD CONSTRAINT `fk_user_ref_attr_03_id` FOREIGN KEY (`attr_03_id`) REFERENCES `app_politic` (`id`),
  ADD CONSTRAINT `fk_user_ref_attr_04_id` FOREIGN KEY (`attr_04_id`) REFERENCES `app_professional_title` (`id`),
  ADD CONSTRAINT `fk_user_ref_avatar_id` FOREIGN KEY (`avatar_id`) REFERENCES `app_user_avatar` (`id`);

--
-- 限制表 `app_users_roles`
--
ALTER TABLE `app_users_roles`
  ADD CONSTRAINT `fk_users_roles_ref_role_id` FOREIGN KEY (`user_id`) REFERENCES `app_user` (`id`),
  ADD CONSTRAINT `fk_users_roles_ref_user_id` FOREIGN KEY (`role_id`) REFERENCES `app_role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
