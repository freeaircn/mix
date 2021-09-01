-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1:3306
-- 生成日期： 2021-08-03 00:42:21
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
  `dataMask` tinyint(11) UNSIGNED DEFAULT NULL COMMENT '数据区域标识',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='部门' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_dept`
--

INSERT INTO `app_dept` (`id`, `name`, `pid`, `status`, `description`, `dataMask`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Mix项目', 0, '1', '无', 0, '2021-06-27 20:00:00', '2021-07-29 18:48:02', NULL),
(2, '松山河口电厂', 1, '1', '无', 1, '2021-06-27 20:00:00', '2021-07-31 21:19:07', NULL),
(3, '测试部', 1, '1', '无', 1, '2021-06-27 20:00:00', '2021-07-29 18:47:42', NULL),
(4, '检修班', 2, '1', '无', 0, '2021-06-27 20:00:00', '2021-07-31 21:18:54', NULL),
(5, '测试一组', 3, '1', '无', 0, '2021-07-29 18:46:25', '2021-07-29 18:47:04', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `app_generator_event_log`
--

DROP TABLE IF EXISTS `app_generator_event_log`;
CREATE TABLE IF NOT EXISTS `app_generator_event_log` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `station_id` tinyint(3) UNSIGNED NOT NULL COMMENT '站点',
  `generator_id` tinyint(3) UNSIGNED NOT NULL COMMENT '机组',
  `event` tinyint(3) UNSIGNED NOT NULL COMMENT '1-停机，2-开机',
  `event_at` datetime DEFAULT NULL COMMENT '时间',
  `run_time` int(11) UNSIGNED DEFAULT 0 COMMENT '运行时间',
  `creator` varchar(7) DEFAULT NULL COMMENT '记录人',
  `description` varchar(127) DEFAULT NULL COMMENT '说明',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='发电机事件' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_generator_event_log`
--

INSERT INTO `app_generator_event_log` (`id`, `station_id`, `generator_id`, `event`, `event_at`, `run_time`, `creator`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(14, 2, 1, 1, '2021-07-26 01:00:00', 0, '小强', NULL, '2021-08-02 00:09:40', '2021-08-02 00:09:40', NULL),
(15, 2, 2, 1, '2021-07-26 02:00:00', 0, '小强', NULL, '2021-08-02 00:09:55', '2021-08-02 00:09:55', NULL),
(16, 2, 3, 1, '2021-07-26 03:00:00', 0, '小强', NULL, '2021-08-02 00:10:03', '2021-08-02 00:10:03', NULL),
(17, 2, 1, 2, '2021-07-26 11:00:00', 0, '小强', NULL, '2021-08-02 00:10:30', '2021-08-02 00:10:30', NULL),
(18, 2, 2, 2, '2021-07-26 11:00:00', 0, '小强', NULL, '2021-08-02 00:10:42', '2021-08-02 00:10:42', NULL),
(19, 2, 3, 2, '2021-07-26 11:00:00', 0, '小强', NULL, '2021-08-02 00:10:45', '2021-08-02 00:10:45', NULL),
(20, 2, 1, 1, '2021-07-26 12:00:00', 3600, '小强', NULL, '2021-08-02 00:11:13', '2021-08-02 00:11:13', NULL),
(21, 2, 2, 1, '2021-07-26 12:00:00', 3600, '小强', NULL, '2021-08-02 00:11:21', '2021-08-02 00:11:21', NULL),
(22, 2, 3, 1, '2021-07-26 12:00:00', 3600, '小强', NULL, '2021-08-02 00:11:25', '2021-08-02 00:11:25', NULL),
(23, 2, 1, 2, '2021-07-27 12:00:00', 0, '小强', NULL, '2021-08-02 00:12:02', '2021-08-02 00:12:02', NULL),
(24, 2, 2, 2, '2021-07-27 12:00:00', 0, '小强', NULL, '2021-08-02 00:12:08', '2021-08-02 00:12:08', NULL),
(25, 2, 1, 1, '2021-07-27 13:00:00', 3600, '小强', NULL, '2021-08-02 00:12:20', '2021-08-02 00:12:20', NULL),
(26, 2, 2, 1, '2021-07-27 13:00:00', 3600, '小强', NULL, '2021-08-02 00:12:27', '2021-08-02 00:12:27', NULL),
(27, 2, 1, 2, '2021-07-28 13:00:00', 0, '小强', NULL, '2021-08-02 00:12:43', '2021-08-02 00:12:43', NULL),
(28, 2, 1, 1, '2021-07-28 14:00:00', 3600, '小强', NULL, '2021-08-02 00:12:58', '2021-08-02 00:12:58', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='岗位' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_job`
--

INSERT INTO `app_job` (`id`, `name`, `status`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '开发员', '1', '无', '2021-06-27 20:00:00', '2021-07-20 02:16:19', NULL),
(2, '测试员', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00', NULL),
(3, '销售员', '1', '无', '2021-06-27 20:00:00', '2021-07-20 02:16:30', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `app_login_attempts`
--

INSERT INTO `app_login_attempts` (`id`, `ip_address`, `identity`, `time`) VALUES
(27, '127.0.0.1', '13812345679', 1627465627),
(28, '127.0.0.1', '13812345679', 1627465630),
(29, '127.0.0.1', '13812345679', 1627465634),
(30, '127.0.0.1', '13812345679', 1627465638);

-- --------------------------------------------------------

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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COMMENT='路由' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_menu`
--

INSERT INTO `app_menu` (`id`, `type`, `pid`, `name`, `path`, `component`, `redirect`, `hidden`, `hideChildrenInMenu`, `meta_hidden`, `title`, `icon`, `keepAlive`, `hiddenHeaderContent`, `permission`, `target`, `authority`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, 0, 'Page', NULL, NULL, NULL, b'0', b'0', b'0', '前端页面', NULL, b'0', b'0', NULL, NULL, NULL, '2021-06-27 20:00:00', NULL, NULL),
(2, 0, 0, 'API', NULL, NULL, NULL, b'0', b'0', b'0', '数据资源', NULL, b'0', b'0', NULL, NULL, NULL, '2021-06-27 20:00:00', NULL, NULL),
(3, 1, 1, 'index', '/', 'BasicLayout', '/dashboard/workplace', b'0', b'0', b'0', '首页', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(4, 1, 3, 'dashboard', '/dashboard', 'RouteView', '/dashboard/workplace', b'0', b'0', b'0', '仪表盘', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(5, 1, 4, 'Workplace', '/dashboard/workplace', 'mix/dashboard/Workplace', '', b'0', b'0', b'0', '工作台', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(6, 1, 4, 'GeneratorEvent', '/dashboard/generator_event', 'mix/dashboard/GeneratorEvent', '', b'0', b'0', b'0', '机组事件', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(9, 1, 3, 'app', '/app', 'RouteView', '/app/role', b'0', b'0', b'0', '应用管理', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(10, 1, 9, 'role', '/app/role', 'mix/admin/role/BasicList', '', b'0', b'0', b'0', '用户角色', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(11, 1, 9, 'dept', '/app/dept', 'mix/admin/dept/index', '', b'0', b'0', b'0', '部门机构', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(12, 1, 9, 'job', '/app/job', 'mix/admin/job/index', '', b'0', b'0', b'0', '工作岗位', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(13, 1, 9, 'title', '/app/title', 'mix/admin/title/index', '', b'0', b'0', b'0', '技能职称', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(14, 1, 9, 'politic', '/app/politic', 'mix/admin/politic/index', '', b'0', b'0', b'0', '政治面貌', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(15, 1, 9, 'user', '/app/user', 'mix/admin/user/Index', '/app/user/list', b'0', b'1', b'0', '注册用户', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(16, 1, 15, 'UserList', '/app/user/list', 'mix/admin/user/UserList', '', b'0', b'0', b'1', '用户列表', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(17, 1, 15, 'UserForm', '/app/user/save/:uid', 'mix/admin/user/UserForm', '', b'0', b'0', b'1', '编辑用户', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(18, 0, 2, 'DataUser', NULL, NULL, NULL, b'0', b'0', b'0', '用户信息', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(19, 2, 18, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'user:get', '2021-06-27 20:00:00', NULL, NULL),
(20, 2, 18, 'Post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'user:post', '2021-06-27 20:00:00', NULL, NULL),
(21, 2, 18, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改', NULL, b'0', b'0', NULL, NULL, 'user:put', '2021-06-27 20:00:00', NULL, NULL),
(22, 2, 18, 'Delete', NULL, NULL, NULL, b'0', b'0', b'0', '删除', NULL, b'0', b'0', NULL, NULL, 'user:delete', '2021-06-27 20:00:00', NULL, NULL),
(23, 0, 2, 'DataRole', NULL, NULL, NULL, b'0', b'0', b'0', '角色数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(24, 2, 23, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'role:get', '2021-06-27 20:00:00', NULL, NULL),
(25, 2, 23, 'Post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'role:post', '2021-06-27 20:00:00', NULL, NULL),
(26, 2, 23, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改', NULL, b'0', b'0', NULL, NULL, 'role:put', '2021-06-27 20:00:00', NULL, NULL),
(27, 2, 23, 'Delete', NULL, NULL, NULL, b'0', b'0', b'0', '删除', NULL, b'0', b'0', NULL, NULL, 'role:delete', '2021-06-27 20:00:00', NULL, NULL),
(28, 0, 2, 'DataDept', NULL, NULL, NULL, b'0', b'0', b'0', '部门数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(29, 2, 28, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'dept:get', '2021-06-27 20:00:00', NULL, NULL),
(30, 2, 28, 'Post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'dept:post', '2021-06-27 20:00:00', NULL, NULL),
(31, 2, 28, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改', NULL, b'0', b'0', NULL, NULL, 'dept:put', '2021-06-27 20:00:00', NULL, NULL),
(32, 2, 28, 'Delete', NULL, NULL, NULL, b'0', b'0', b'0', '删除', NULL, b'0', b'0', NULL, NULL, 'dept:delete', '2021-06-27 20:00:00', NULL, NULL),
(33, 0, 2, 'DataJob', NULL, NULL, NULL, b'0', b'0', b'0', '岗位数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(34, 2, 33, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'job:get', '2021-06-27 20:00:00', NULL, NULL),
(35, 2, 33, 'Post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'job:post', '2021-06-27 20:00:00', NULL, NULL),
(36, 2, 33, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改', NULL, b'0', b'0', NULL, NULL, 'job:put', '2021-06-27 20:00:00', NULL, NULL),
(37, 2, 33, 'Delete', NULL, NULL, NULL, b'0', b'0', b'0', '删除', NULL, b'0', b'0', NULL, NULL, 'job:delete', '2021-06-27 20:00:00', NULL, NULL),
(38, 0, 2, 'DataTitle', NULL, NULL, NULL, b'0', b'0', b'0', '职称数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(39, 2, 38, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'title:get', '2021-06-27 20:00:00', NULL, NULL),
(40, 2, 38, 'Post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'title:post', '2021-06-27 20:00:00', NULL, NULL),
(41, 2, 38, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改', NULL, b'0', b'0', NULL, NULL, 'title:put', '2021-06-27 20:00:00', NULL, NULL),
(42, 2, 38, 'Delete', NULL, NULL, NULL, b'0', b'0', b'0', '删除', NULL, b'0', b'0', NULL, NULL, 'title:delete', '2021-06-27 20:00:00', NULL, NULL),
(43, 0, 2, 'DataPolitic', NULL, NULL, NULL, b'0', b'0', b'0', '政治面貌数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(44, 2, 43, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'politic:get', '2021-06-27 20:00:00', NULL, NULL),
(45, 2, 43, 'Post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'politic:post', '2021-06-27 20:00:00', NULL, NULL),
(46, 2, 43, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改', NULL, b'0', b'0', NULL, NULL, 'politic:put', '2021-06-27 20:00:00', NULL, NULL),
(47, 2, 43, 'Delete', NULL, NULL, NULL, b'0', b'0', b'0', '删除', NULL, b'0', b'0', NULL, NULL, 'politic:delete', '2021-06-27 20:00:00', NULL, NULL),
(48, 0, 2, 'DataMenu', NULL, NULL, NULL, b'0', b'0', b'0', '菜单数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(49, 2, 48, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'menu:get', '2021-06-27 20:00:00', NULL, NULL),
(50, 0, 2, 'DataRoleMenu', NULL, NULL, NULL, b'0', b'0', b'0', '角色-菜单数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(51, 2, 50, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'role_menu:get', '2021-06-27 20:00:00', NULL, NULL),
(52, 2, 50, 'Post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'role_menu:post', '2021-06-27 20:00:00', NULL, NULL),
(53, 0, 2, 'DataUserMenu', NULL, NULL, NULL, b'0', b'0', b'0', '用户-角色数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(54, 2, 53, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'user_role:get', '2021-06-27 20:00:00', NULL, NULL),
(55, 1, 3, 'Account', '/account', 'RouteView', '/account/settings', b'0', b'0', b'0', '个人页', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(56, 1, 55, 'Settings', '/account/settings', 'mix/account/settings/Index', '/account/settings/basic', b'0', b'1', b'0', '个人设置', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(57, 1, 56, 'BasicSettings', '/account/settings/basic', 'mix/account/settings/BasicSetting', '', b'0', b'0', b'1', '个人信息', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(58, 1, 56, 'SecuritySettings', '/account/settings/security', 'mix/account/settings/Security', '', b'0', b'0', b'1', '安全设置', '', b'0', b'0', '', '', '', '2021-06-27 20:00:00', NULL, NULL),
(59, 0, 2, 'DataAccount', NULL, NULL, NULL, b'0', b'0', b'0', '用户个人数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(60, 2, 59, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询个人信息', NULL, b'0', b'0', NULL, NULL, 'account/info:get', '2021-06-27 20:00:00', NULL, NULL),
(61, 2, 59, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询授权页面', NULL, b'0', b'0', NULL, NULL, 'account/menus:get', '2021-06-27 20:00:00', NULL, NULL),
(62, 2, 59, 'post', NULL, NULL, NULL, b'0', b'0', b'0', '新建头像', NULL, b'0', b'0', NULL, NULL, 'account/avatar:post', '2021-06-27 20:00:00', NULL, NULL),
(63, 2, 59, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改个人信息', NULL, b'0', b'0', NULL, NULL, 'account:put', '2021-06-27 20:00:00', NULL, NULL),
(64, 2, 59, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改登录密码', NULL, b'0', b'0', NULL, NULL, 'account/password:put', '2021-06-27 20:00:00', NULL, NULL),
(65, 2, 59, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改手机号', NULL, b'0', b'0', NULL, NULL, 'account/phone:put', '2021-06-27 20:00:00', NULL, NULL),
(66, 2, 59, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '提交验证码', NULL, b'0', b'0', NULL, NULL, 'account/sms:put', '2021-06-27 20:00:00', NULL, NULL),
(67, 2, 59, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改邮箱', NULL, b'0', b'0', NULL, NULL, 'account/email:put', '2021-06-27 20:00:00', NULL, NULL),
(68, 0, 2, 'DataGeneratorEvent', NULL, NULL, NULL, b'0', b'0', b'0', '机组事件数据', NULL, b'0', b'0', NULL, NULL, '', '2021-06-27 20:00:00', NULL, NULL),
(69, 2, 68, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询', NULL, b'0', b'0', NULL, NULL, 'generator/event:get', '2021-06-27 20:00:00', NULL, NULL),
(70, 2, 68, 'post', NULL, NULL, NULL, b'0', b'0', b'0', '新建', NULL, b'0', b'0', NULL, NULL, 'generator/event:post', '2021-06-27 20:00:00', NULL, NULL),
(71, 2, 68, 'put', NULL, NULL, NULL, b'0', b'0', b'0', '修改', NULL, b'0', b'0', NULL, NULL, 'generator/event:put', '2021-06-27 20:00:00', NULL, NULL),
(72, 2, 68, 'delete', NULL, NULL, NULL, b'0', b'0', b'0', '删除', NULL, b'0', b'0', NULL, NULL, 'generator/event:delete', '2021-06-27 20:00:00', NULL, NULL),
(73, 2, 68, 'Get', NULL, NULL, NULL, b'0', b'0', b'0', '查询事件统计', NULL, b'0', b'0', NULL, NULL, 'generator/event/statistic:get', '2021-06-27 20:00:00', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='政治面貌' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_politic`
--

INSERT INTO `app_politic` (`id`, `name`, `status`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '群众', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00', NULL),
(2, '中共党员', '0', '无', '2021-06-27 20:00:00', '2021-07-15 17:12:03', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='角色表' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_role`
--

INSERT INTO `app_role` (`id`, `name`, `alias`, `status`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '管理组', 'admin_group', '1', '具有管理员权限', '2021-06-27 20:00:00', '2021-07-03 15:29:25', NULL),
(2, '开发组', 'develop_group', '0', '具有开发员的权限', '2021-06-27 20:00:00', '2021-07-15 17:14:37', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `app_role_menu`
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
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 49),
(1, 51),
(1, 52),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(1, 58),
(1, 60),
(1, 61),
(1, 62),
(1, 63),
(1, 64),
(1, 65),
(1, 66),
(1, 67),
(1, 69),
(1, 70),
(1, 71),
(1, 72),
(1, 73);

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='职称' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_title`
--

INSERT INTO `app_title` (`id`, `name`, `status`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '工程师', '1', '无', '2021-06-27 20:00:00', '2021-07-14 19:40:48', NULL),
(2, '助理工程师', '1', '无', '2021-06-27 20:00:00', '2021-06-27 20:00:00', NULL);

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
  `last_login` datetime DEFAULT NULL COMMENT '日期',
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='用户' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user`
--

INSERT INTO `app_user` (`id`, `workID`, `username`, `sex`, `IdCard`, `phone`, `email`, `status`, `forceChgPwd`, `avatar`, `deptLev0`, `deptLev1`, `deptLev2`, `deptLev3`, `deptLev4`, `deptLev5`, `deptLev6`, `deptLev7`, `job`, `title`, `politic`, `ip_address`, `last_login`, `created_at`, `updated_at`, `deleted_at`, `id01`, `id02`, `id03`, `id04`, `id05`, `id06`, `id07`, `id08`, `id09`, `id10`, `id11`, `id12`, `id13`, `id14`, `id15`, `id16`, `str01`, `str02`, `str03`, `str04`, `str05`, `str06`, `str07`, `str08`, `str09`, `str10`, `str11`, `str12`, `str13`, `str14`, `str15`, `str16`, `password`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`) VALUES
(1, '1', '小强', '男', '', '13812345678', '1@1.1', '1', '0', 1, 1, 2, 4, 0, 0, 0, 0, 0, 1, 1, 2, '127.0.0.1', '2021-08-02 22:03:36', '2021-06-27 20:00:00', '2021-08-02 22:03:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$argon2i$v=19$m=16384,t=4,p=2$Q0ZXc2NIWFJrcUxKSzA5UQ$Qt//SEIMKQDKVKsLzztLnTbVZQs/ysxKKhls908T0hQ', NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='头像' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user_avatar`
--

INSERT INTO `app_user_avatar` (`id`, `name`, `path`, `size`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1627138101_cad105bcde692a47a60f.jpg', 'avatar/user/', NULL, '2021-06-27 20:00:00', '2021-07-24 22:48:21', NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户角色关联' ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `app_user_role`
--

INSERT INTO `app_user_role` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2);

--
-- 表的结构 `app_meter`
--

DROP TABLE IF EXISTS `app_meter`;
CREATE TABLE IF NOT EXISTS `app_meter` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `station_id` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '站点',
  `log_date` date DEFAULT NULL,
  `log_time` time DEFAULT NULL,
  `meter_id` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '表编号',
  
  `fak` int(11) UNSIGNED DEFAULT 0 COMMENT '正向有功',
  `bak` int(11) UNSIGNED DEFAULT 0 COMMENT '反向有功',
  `frk` int(11) UNSIGNED DEFAULT 0 COMMENT '正向无功',
  `brk` int(11) UNSIGNED DEFAULT 0 COMMENT '反向无功',
  `peak` int(11) UNSIGNED DEFAULT 0 COMMENT '高峰',
  `valley` int(11) UNSIGNED DEFAULT 0 COMMENT '低谷',
  
  `fak_delta` int(11) UNSIGNED DEFAULT 0 COMMENT '正向有功',
  `bak_delta` int(11) UNSIGNED DEFAULT 0 COMMENT '反向有功',
  `frk_delta` int(11) UNSIGNED DEFAULT 0 COMMENT '正向无功',
  `brk_delta` int(11) UNSIGNED DEFAULT 0 COMMENT '反向无功',
  `peak_delta` int(11) UNSIGNED DEFAULT 0 COMMENT '高峰',
  `valley_delta` int(11) UNSIGNED DEFAULT 0 COMMENT '低谷',
  
  `creator` varchar(7) DEFAULT NULL COMMENT '记录人',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='电度表' ROW_FORMAT=COMPACT;

DROP TABLE IF EXISTS `app_kwh_planning`;
CREATE TABLE IF NOT EXISTS `app_kwh_planning` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `station_id` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '站点',
  `year` smallint(4) UNSIGNED DEFAULT 0 COMMENT '年份',
  `month` smallint(2) UNSIGNED DEFAULT 0 COMMENT '月份',
  `planning` int(11) UNSIGNED DEFAULT 0 COMMENT '计划',
  `deal` int(11) UNSIGNED DEFAULT 0 COMMENT '成交',
  
  `creator` varchar(7) DEFAULT NULL COMMENT '记录人',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='计划发电量' ROW_FORMAT=COMPACT;


--
-- 数据库： `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
