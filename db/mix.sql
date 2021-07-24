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
-- 数据库： `mix`
--
CREATE DATABASE IF NOT EXISTS `mix` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `mix`;

--
-- 表的结构 `app_menu`
--

DROP TABLE IF EXISTS `app_menu`;
CREATE TABLE IF NOT EXISTS `app_menu` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` int(11) UNSIGNED NOT NULL COMMENT '0-辅助展示，1-页面，2-API',
   `pid` int(11) UNSIGNED NOT NULL COMMENT '上级菜单ID',
  
  `name` varchar(63) DEFAULT NULL COMMENT '路由名称',
  `path` varchar(63) DEFAULT NULL COMMENT '路由Path',
  `component` varchar(127) DEFAULT NULL COMMENT '组件',
  `redirect` varchar(127) DEFAULT NULL COMMENT '重定向',
  `hidden` bit(1) NOT NULL DEFAULT b'0' COMMENT '侧边栏隐藏',
  `hideChildrenInMenu` bit(1) NOT NULL DEFAULT b'0' COMMENT '强制菜单显示为Item',
  `meta_hidden` bit(1) NOT NULL DEFAULT b'0' COMMENT '侧边栏隐藏，配合hideChildrenInMenu',

  `title` varchar(31) NOT NULL COMMENT '菜单标题',
  `icon` varchar(63) DEFAULT NULL COMMENT '图标',
  `keepAlive` bit(1) NOT NULL DEFAULT b'0' COMMENT '缓存该路由 (开启 multi-tab 是默认值为 true)',
  `hiddenHeaderContent` bit(1) NOT NULL DEFAULT b'0' COMMENT '隐藏面包屑和页面标题栏',
  `permission` varchar(63) DEFAULT NULL COMMENT 'antd权限',
  `target` varchar(63) DEFAULT NULL COMMENT '打开到新窗口',
  
  
  `authority` varchar(63) DEFAULT NULL COMMENT 'Mix权限',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `key_menu_pid` (`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='路由' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_menu`
--

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `created_at`) VALUES
(1, 0, 0, 'Page', '前端页面', '2021-06-27 20:00:00'),
(2, 0, 0, 'API', '数据资源', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `path`, `component`, `redirect`, `hidden`, `hideChildrenInMenu`, `meta_hidden`, `title`, `icon`, `keepAlive`, `hiddenHeaderContent`, `permission`, `target`, `authority`, `created_at`) VALUES
(3, 1, 1, 'index', '/', 'BasicLayout', '/dashboard/workplace', b'0', b'0', b'0', '首页', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),

(4, 1, 3, 'dashboard', '/dashboard', 'RouteView', '/dashboard/workplace', b'0', b'0', b'0', '仪表盘', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(5, 1, 4, 'Workplace', '/dashboard/workplace', 'dashboard/Workplace', '', b'0', b'0', b'0', '工作台', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(6, 1, 4, 'External', 'https://www.baidu.com/', '', '', b'0', b'0', b'0', '外部链接', '', b'0', b'0', '', '_blank', '', '2021-06-27 20:00:00'),

(9, 1, 3, 'app', '/app', 'RouteView', '/app/role', b'0', b'0', b'0', '应用管理', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(10, 1, 9, 'role', '/app/role', 'app_admin/role/BasicList', '', b'0', b'0', b'0', '用户角色', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(11, 1, 9, 'dept', '/app/dept', 'app_admin/dept/index', '', b'0', b'0', b'0', '部门机构', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(12, 1, 9, 'job', '/app/job', 'app_admin/job/index', '', b'0', b'0', b'0', '工作岗位', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(13, 1, 9, 'title', '/app/title', 'app_admin/title/index', '', b'0', b'0', b'0', '技能职称', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(14, 1, 9, 'politic', '/app/politic', 'app_admin/politic/index', '', b'0', b'0', b'0', '政治面貌', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(15, 1, 9, 'user', '/app/user', 'app_admin/user/Index', '/app/user/list', b'0', b'1', b'0', '注册用户', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(16, 1, 15, 'UserList', '/app/user/list', 'app_admin/user/UserList', '', b'0', b'0', b'1', '用户列表', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(17, 1, 15, 'UserForm', '/app/user/save/:uid', 'app_admin/user/UserForm', '', b'0', b'0', b'1', '编辑用户', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(18, 0, 2, 'DataUser', '用户信息', '', '2021-06-27 20:00:00'),
(19, 2, 18, 'Get', '查询', 'user:get', '2021-06-27 20:00:00'),
(20, 2, 18, 'Post', '新建', 'user:post', '2021-06-27 20:00:00'),
(21, 2, 18, 'put', '修改', 'user:put', '2021-06-27 20:00:00'),
(22, 2, 18, 'Delete', '删除', 'user:delete', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(23, 0, 2, 'DataRole', '角色数据', '', '2021-06-27 20:00:00'),
(24, 2, 23, 'Get', '查询', 'role:get', '2021-06-27 20:00:00'),
(25, 2, 23, 'Post', '新建', 'role:post', '2021-06-27 20:00:00'),
(26, 2, 23, 'put', '修改', 'role:put', '2021-06-27 20:00:00'),
(27, 2, 23, 'Delete', '删除', 'role:delete', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(28, 0, 2, 'DataDept', '部门数据', '', '2021-06-27 20:00:00'),
(29, 2, 28, 'Get', '查询', 'dept:get', '2021-06-27 20:00:00'),
(30, 2, 28, 'Post', '新建', 'dept:post', '2021-06-27 20:00:00'),
(31, 2, 28, 'put', '修改', 'dept:put', '2021-06-27 20:00:00'),
(32, 2, 28, 'Delete', '删除', 'dept:delete', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(33, 0, 2, 'DataJob', '岗位数据', '', '2021-06-27 20:00:00'),
(34, 2, 33, 'Get', '查询', 'job:get', '2021-06-27 20:00:00'),
(35, 2, 33, 'Post', '新建', 'job:post', '2021-06-27 20:00:00'),
(36, 2, 33, 'put', '修改', 'job:put', '2021-06-27 20:00:00'),
(37, 2, 33, 'Delete', '删除', 'job:delete', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(38, 0, 2, 'DataTitle', '职称数据', '', '2021-06-27 20:00:00'),
(39, 2, 38, 'Get', '查询', 'title:get', '2021-06-27 20:00:00'),
(40, 2, 38, 'Post', '新建', 'title:post', '2021-06-27 20:00:00'),
(41, 2, 38, 'put', '修改', 'title:put', '2021-06-27 20:00:00'),
(42, 2, 38, 'Delete', '删除', 'title:delete', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(43, 0, 2, 'DataPolitic', '政治面貌数据', '', '2021-06-27 20:00:00'),
(44, 2, 43, 'Get', '查询', 'politic:get', '2021-06-27 20:00:00'),
(45, 2, 43, 'Post', '新建', 'politic:post', '2021-06-27 20:00:00'),
(46, 2, 43, 'put', '修改', 'politic:put', '2021-06-27 20:00:00'),
(47, 2, 43, 'Delete', '删除', 'politic:delete', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(48, 0, 2, 'DataMenu', '菜单数据', '', '2021-06-27 20:00:00'),
(49, 2, 48, 'Get', '查询', 'menu:get', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(50, 0, 2, 'DataRoleMenu', '角色-菜单数据', '', '2021-06-27 20:00:00'),
(51, 2, 50, 'Get', '查询', 'role_menu:get', '2021-06-27 20:00:00'),
(52, 2, 50, 'Post', '新建', 'role_menu:post', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(53, 0, 2, 'DataUserMenu', '用户-角色数据', '', '2021-06-27 20:00:00'),
(54, 2, 53, 'Get', '查询', 'user_role:get', '2021-06-27 20:00:00');


INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `path`, `component`, `redirect`, `hidden`, `hideChildrenInMenu`, `meta_hidden`, `title`, `icon`, `keepAlive`, `hiddenHeaderContent`, `permission`, `target`, `authority`, `created_at`) VALUES
(55, 1, 3, 'Account', '/account', 'RouteView', '/account/settings', b'0', b'0', b'0', '个人页', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(56, 1, 55, 'Settings', '/account/settings', 'app_account/settings/Index', '/account/settings/basic', b'0', b'1', b'0', '个人设置', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(57, 1, 56, 'BasicSettings', '/account/settings/basic', 'app_account/settings/BasicSetting', '', b'0', b'0', b'1', '个人信息', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00'),
(58, 1, 56, 'SecuritySettings', '/account/settings/security', 'app_account/settings/Security', '', b'0', b'0', b'1', '安全设置', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00');


INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(59, 0, 2, 'DataAccount', '用户个人数据', '', '2021-06-27 20:00:00'),
(60, 2, 59, 'Get', '查询', 'account:get', '2021-06-27 20:00:00'),
(61, 2, 59, 'put', '修改', 'account:put', '2021-06-27 20:00:00');

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `title`, `authority`, `created_at`) VALUES
(62, 2, 59, 'post', '新建', 'account:post', '2021-06-27 20:00:00');


-- --------------------------------------------------------

--
-- 表的结构 `app_role`
--

DROP TABLE IF EXISTS `app_role`;
CREATE TABLE IF NOT EXISTS `app_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(31) NOT NULL COMMENT '名称',
  `alias` varchar(63) NOT NULL COMMENT '别名',
  `status` varchar(31) NOT NULL COMMENT '状态',
  `description` varchar(127) DEFAULT NULL COMMENT '说明',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色表' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_role`
--

INSERT INTO `app_role` (`id`, `name`, `alias`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, '管理组', 'admin_group', '1', '具有管理员权限', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(2, '开发组', 'develop_group', '1', '具有开发员的权限', '2021-06-27 20:00:00', '2021-06-27 20:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `app_roles_menus`
--

DROP TABLE IF EXISTS `app_role_menu`;
CREATE TABLE IF NOT EXISTS `app_role_menu` (
  `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
  `menu_id` int(11) UNSIGNED NOT NULL COMMENT '菜单ID',
  PRIMARY KEY (`role_id`,`menu_id`) USING BTREE,
  KEY `fk_roles_menus_ref_role_id` (`role_id`) USING BTREE,
  KEY `fk_roles_menus_ref_menu_id` (`menu_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色菜单关联' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_role_menu`
--

INSERT INTO `app_role_menu` (`role_id`, `menu_id`) VALUES
(1, 1);


-- --------------------------------------------------------

--
-- 表的结构 `app_dept`
--

DROP TABLE IF EXISTS `app_dept`;
CREATE TABLE IF NOT EXISTS `app_dept` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(63) NOT NULL COMMENT '名称',
  `pid` int(11) UNSIGNED NOT NULL COMMENT '上级节点',
  `status` varchar(31) NOT NULL COMMENT '状态',
  `description` varchar(127) DEFAULT NULL COMMENT '说明',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='部门' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_dept`
--

INSERT INTO `app_dept` (`id`, `name`, `pid`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Mix项目', 0, '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(2, '开发组', 1, '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(3, '测试组', 1, '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(4, '开发一组', 2, '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `app_job`
--

DROP TABLE IF EXISTS `app_job`;
CREATE TABLE IF NOT EXISTS `app_job` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(63) NOT NULL COMMENT '名称',
  `status` varchar(31) NOT NULL COMMENT '状态',
  `description` varchar(127) DEFAULT NULL COMMENT '说明',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='岗位' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_job`
--

INSERT INTO `app_job` (`id`, `name`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, '开发员', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(2, '测试员', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(3, '销售员', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00');


-- --------------------------------------------------------

--
-- 表的结构 `app_title`
--

DROP TABLE IF EXISTS `app_title`;
CREATE TABLE IF NOT EXISTS `app_title` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(63) NOT NULL COMMENT '名称',
  `status` varchar(31) NOT NULL COMMENT '状态',
  `description` varchar(127) DEFAULT NULL COMMENT '说明',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='职称' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_title`
--

INSERT INTO `app_title` (`id`, `name`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, '工程师', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(2, '助理工程师', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00');


-- --------------------------------------------------------

--
-- 表的结构 `app_politic`
--

DROP TABLE IF EXISTS `app_politic`;
CREATE TABLE IF NOT EXISTS `app_politic` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(63) NOT NULL COMMENT '名称',
  `status` varchar(31) NOT NULL COMMENT '状态',
  `description` varchar(127) DEFAULT NULL COMMENT '说明',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='政治面貌' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_politic`
--

INSERT INTO `app_politic` (`id`, `name`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, '群众', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(2, '中共党员', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00');


-- --------------------------------------------------------

--
-- 表的结构 `app_user`
--

DROP TABLE IF EXISTS `app_user`;
CREATE TABLE IF NOT EXISTS `app_user` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `workID` varchar(63) NOT NULL COMMENT '工号',
  `username` varchar(10) NOT NULL COMMENT '中文名',
  `sex` varchar(3) NOT NULL COMMENT '女，男',
  `IdCard` varchar(31) DEFAULT NULL COMMENT '证件',
  `phone` varchar(15) NOT NULL,
  `email` varchar(63) NOT NULL,
  `status` varchar(3) NOT NULL COMMENT '启用或禁用',
  `forceChgPwd` varchar(3) NOT NULL DEFAULT '1' COMMENT '要求修改密码',
  `avatar` int(11) UNSIGNED DEFAULT NULL COMMENT '头像ID',
  
  `deptLev0` int(11) DEFAULT 0 COMMENT '部门0',
  `deptLev1` int(11) DEFAULT 0 COMMENT '部门1',
  `deptLev2` int(11) DEFAULT 0 COMMENT '部门2',
  `deptLev3` int(11) DEFAULT 0 COMMENT '部门3',
  `deptLev4` int(11) DEFAULT 0 COMMENT '部门4',
  `deptLev5` int(11) DEFAULT 0 COMMENT '部门5',
  `deptLev6` int(11) DEFAULT 0 COMMENT '部门6',
  `deptLev7` int(11) DEFAULT 0 COMMENT '部门7',
  `job` int(11) UNSIGNED DEFAULT NULL COMMENT '岗位ID',
  `title` int(11) UNSIGNED DEFAULT NULL COMMENT '职称ID',
  `politic` int(11) UNSIGNED DEFAULT NULL COMMENT '政治面貌ID',
  
  `ip_address` varchar(63) DEFAULT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  
  `created_at` datetime DEFAULT NULL COMMENT '日期',
  `updated_at` datetime DEFAULT NULL COMMENT '日期',
  `deleted_at` datetime DEFAULT NULL COMMENT '日期',
  
  `id01` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id02` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id03` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id04` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id05` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id06` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id07` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id08` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id09` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id10` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id11` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id12` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id13` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id14` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id15` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',
  `id16` int(11) UNSIGNED DEFAULT NULL COMMENT '预留',

  `str01` varchar(63) DEFAULT NULL COMMENT '预留',
  `str02` varchar(63) DEFAULT NULL COMMENT '预留',
  `str03` varchar(63) DEFAULT NULL COMMENT '预留',
  `str04` varchar(63) DEFAULT NULL COMMENT '预留',
  `str05` varchar(63) DEFAULT NULL COMMENT '预留',
  `str06` varchar(63) DEFAULT NULL COMMENT '预留',
  `str07` varchar(63) DEFAULT NULL COMMENT '预留',
  `str08` varchar(63) DEFAULT NULL COMMENT '预留',
  `str09` varchar(63) DEFAULT NULL COMMENT '预留',
  `str10` varchar(63) DEFAULT NULL COMMENT '预留',
  `str11` varchar(63) DEFAULT NULL COMMENT '预留',
  `str12` varchar(63) DEFAULT NULL COMMENT '预留',
  `str13` varchar(63) DEFAULT NULL COMMENT '预留',
  `str14` varchar(63) DEFAULT NULL COMMENT '预留',
  `str15` varchar(63) DEFAULT NULL COMMENT '预留',
  `str16` varchar(63) DEFAULT NULL COMMENT '预留',

  `password` varchar(255) NOT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uc_phone` (`phone`) USING BTREE,
  UNIQUE KEY `uc_email` (`email`) USING BTREE,
  UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`) USING BTREE,
  KEY `key_username` (`username`) USING BTREE,
  
  KEY `key_user_deptLev0` (`deptLev0`) USING BTREE,
  KEY `key_user_deptLev1` (`deptLev1`) USING BTREE,
  KEY `key_user_deptLev2` (`deptLev2`) USING BTREE,
  KEY `key_user_deptLev3` (`deptLev3`) USING BTREE,
  KEY `key_user_deptLev4` (`deptLev4`) USING BTREE,
  KEY `key_user_deptLev5` (`deptLev5`) USING BTREE,
  KEY `key_user_deptLev6` (`deptLev6`) USING BTREE,
  KEY `key_user_deptLev7` (`deptLev7`) USING BTREE,
  
  KEY `key_user_ref_job` (`job`) USING BTREE,
  KEY `key_user_ref_title` (`title`) USING BTREE,
  KEY `key_user_ref_politic` (`politic`) USING BTREE,
  
  KEY `key_user_ref_id01` (`id01`) USING BTREE,
  KEY `key_user_ref_id02` (`id02`) USING BTREE,
  KEY `key_user_ref_id03` (`id03`) USING BTREE,
  KEY `key_user_ref_id04` (`id04`) USING BTREE,
  KEY `key_user_ref_id05` (`id05`) USING BTREE,
  KEY `key_user_ref_id06` (`id06`) USING BTREE,
  KEY `key_user_ref_id07` (`id07`) USING BTREE,
  KEY `key_user_ref_id08` (`id08`) USING BTREE,
  KEY `key_user_ref_id09` (`id09`) USING BTREE,
  KEY `key_user_ref_id10` (`id10`) USING BTREE,
  KEY `key_user_ref_id11` (`id11`) USING BTREE,
  KEY `key_user_ref_id12` (`id12`) USING BTREE,
  KEY `key_user_ref_id13` (`id13`) USING BTREE,
  KEY `key_user_ref_id14` (`id14`) USING BTREE,
  KEY `key_user_ref_id15` (`id15`) USING BTREE,
  KEY `key_user_ref_id16` (`id16`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user`
--

INSERT INTO `app_user` (`id`, `workID`, `username`, `sex`, `IdCard`, `phone`, `email`, `status`, `password`, `forceChgPwd`, `created_at`, `updated_at`) VALUES
(1, '1', '小明', '男', '', '13812345678', '3@3.3', '1', '$argon2i$v=19$m=16384,t=4,p=2$My9kc2ZXZHUvNkx1ZjFCdA$6ZcgoVANOcxp7g6qyGO5ICw9w94Rhbapk8Nz92zB9bk', '0', '2021-06-27 20:00:00', '2021-06-27 20:00:00');


-- --------------------------------------------------------

--
-- 表的结构 `app_user_role`
--

DROP TABLE IF EXISTS `app_user_role`;
CREATE TABLE IF NOT EXISTS `app_user_role` (
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`user_id`,`role_id`) USING BTREE,
  KEY `key_user_role_user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户角色关联' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user_role`
--

INSERT INTO `app_user_role` (`user_id`, `role_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `app_login_attempts`
--

DROP TABLE IF EXISTS `app_login_attempts`;
CREATE TABLE IF NOT EXISTS `app_login_attempts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `identity` varchar(63) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `app_login_attempts`
--


-- --------------------------------------------------------

--
-- 表的结构 `app_user_avatar`
--

DROP TABLE IF EXISTS `app_user_avatar`;
CREATE TABLE IF NOT EXISTS `app_user_avatar` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '文件名',
  `path` varchar(255) DEFAULT NULL COMMENT '路径',
  `size` varchar(255) DEFAULT NULL COMMENT '大小',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='头像' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user_avatar`
--

INSERT INTO `app_user_avatar` (`id`, `name`, `path`, `size`, `created_at`, `updated_at`) VALUES
(1, 'male.jpg', 'avatar/default/', NULL, '2021-06-27 20:00:00', '2021-06-27 20:00:00'),
(2, 'female.jpg', 'avatar/default/', NULL, '2021-06-27 20:00:00', '2021-06-27 20:00:00');


-- --------------------------------------------------------

--
-- 表的结构 `app_sms_code`
--

DROP TABLE IF EXISTS `app_sms_code`;
CREATE TABLE IF NOT EXISTS `app_sms_code` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` varchar(15) NOT NULL,
  `code` varchar(5) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uc_sms_code_phone` (`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
