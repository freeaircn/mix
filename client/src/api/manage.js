/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-13 22:49:02
 */
import request from '@/utils/request'

const api = {
  user: '/user',
  role: '/role',
  menu: '/menu',
  roleMenu: '/role_menu',
  dept: '/dept',
  job: '/job',
  title: '/title',
  politic: '/politic',
  userRole: '/user_role',
  // user: '/user',
  //
  service: '/service',
  permission: '/permission',
  permissionNoPager: '/permission/no-pager',
  orgTree: '/org/tree'
}

export default api

export function getUserList (parameter) {
  return request({
    url: api.user,
    method: 'get',
    params: parameter
  })
}

export function getRoleList (parameter) {
  return request({
    url: api.role,
    method: 'get',
    params: parameter
  })
}

export function getServiceList (parameter) {
  return request({
    url: api.service,
    method: 'get',
    params: parameter
  })
}

export function getPermissions (parameter) {
  return request({
    url: api.permissionNoPager,
    method: 'get',
    params: parameter
  })
}

export function getOrgTree (parameter) {
  return request({
    url: api.orgTree,
    method: 'get',
    params: parameter
  })
}

// id == 0 add     post
// id != 0 update  put
export function saveService (parameter) {
  return request({
    url: api.service,
    method: parameter.id === 0 ? 'post' : 'put',
    data: parameter
  })
}

export function saveSub (sub) {
  return request({
    url: '/sub',
    method: sub.id === 0 ? 'post' : 'put',
    data: sub
  })
}

// Mix code
// 角色
export function getRoleTbl (parameter) {
  return request({
    url: api.role,
    method: 'get',
    params: parameter
  })
}

export function saveRole (sub) {
  return request({
    url: api.role,
    method: sub.id && sub.id > 0 ? 'put' : 'post',
    data: sub
  })
}

export function delRole (id) {
  return request({
    url: api.role,
    method: 'delete',
    data: { id }
  })
}

// 菜单
export function getMenu () {
  return request({
    url: api.menu,
    method: 'get'
  })
}

//
export function getRoleMenu (parameter) {
  return request({
    url: api.roleMenu,
    method: 'get',
    params: parameter
  })
}

export function saveRoleMenu (data) {
  return request({
    url: api.roleMenu,
    method: 'post',
    data: data
  })
}

// 部门
export function getDeptTbl (parameter) {
  return request({
    url: api.dept,
    method: 'get',
    params: parameter
  })
}

export function saveDept (data) {
  return request({
    url: api.dept,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delDept (id) {
  return request({
    url: api.dept,
    method: 'delete',
    data: { id }
  })
}

// 岗位
export function getJobTbl (parameter) {
  return request({
    url: api.job,
    method: 'get',
    params: parameter
  })
}

export function saveJob (data) {
  return request({
    url: api.job,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delJob (id) {
  return request({
    url: api.job,
    method: 'delete',
    data: { id }
  })
}

// 职称
export function getTitleTbl (parameter) {
  return request({
    url: api.title,
    method: 'get',
    params: parameter
  })
}

export function saveTitle (data) {
  return request({
    url: api.title,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delTitle (id) {
  return request({
    url: api.title,
    method: 'delete',
    data: { id }
  })
}

// 政治面貌
export function getPoliticTbl (parameter) {
  return request({
    url: api.politic,
    method: 'get',
    params: parameter
  })
}

export function savePolitic (data) {
  return request({
    url: api.politic,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delPolitic (id) {
  return request({
    url: api.politic,
    method: 'delete',
    data: { id }
  })
}

// 用户
export function getUserTbl (parameter) {
  return request({
    url: api.user,
    method: 'get',
    params: parameter
  })
}

export function saveUser (data) {
  return request({
    url: api.user,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delUser (id) {
  return request({
    url: api.user,
    method: 'delete',
    data: { id }
  })
}

// 用户-角色
export function getUserRole (parameter) {
  return request({
    url: api.userRole,
    method: 'get',
    params: parameter
  })
}
