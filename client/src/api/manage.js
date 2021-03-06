/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-12 11:27:51
 */
import request from '@/utils/request'

const api = {
  user: '/user',
  role: '/role',
  menu: '/menu',
  api: '/api',
  dept: '/dept',
  workflow: '/workflow',
  //
  roleMenu: '/role_menu',
  roleApi: '/role_api',
  roleDept: '/role_dept',
  roleWorkflow: '/role_workflow',
  //
  job: '/job',
  title: '/title',
  politic: '/politic',
  userRole: '/user_role',
  equipmentUnit: '/equipment_unit',
  WorkflowAuthority: '/workflow/authority',
  roleWorkflowAuthority: '/role_workflow_authority'
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

// export function getServiceList (parameter) {
//   return request({
//     url: api.service,
//     method: 'get',
//     params: parameter
//   })
// }

// export function getPermissions (parameter) {
//   return request({
//     url: api.permissionNoPager,
//     method: 'get',
//     params: parameter
//   })
// }

// export function getOrgTree (parameter) {
//   return request({
//     url: api.orgTree,
//     method: 'get',
//     params: parameter
//   })
// }

// id == 0 add     post
// id != 0 update  put
// export function saveService (parameter) {
//   return request({
//     url: api.service,
//     method: parameter.id === 0 ? 'post' : 'put',
//     data: parameter
//   })
// }

export function saveSub (sub) {
  return request({
    url: '/sub',
    method: sub.id === 0 ? 'post' : 'put',
    data: sub
  })
}

// My code
// 角色
export function getRoleTbl (params) {
  return request({
    url: api.role,
    method: 'get',
    params: params
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
export function getMenu (params) {
  return request({
    url: api.menu,
    method: 'get',
    params: params
  })
}

export function saveMenu (data) {
  return request({
    url: api.menu,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delMenu (id) {
  return request({
    url: api.menu,
    method: 'delete',
    data: { id }
  })
}

// API
export function getApi () {
  return request({
    url: api.api,
    method: 'get'
  })
}

export function saveApi (data) {
  return request({
    url: api.api,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delApi (id) {
  return request({
    url: api.api,
    method: 'delete',
    data: { id }
  })
}

// Workflow
export function getWorkflow () {
  return request({
    url: api.workflow,
    method: 'get'
  })
}

export function saveWorkflow (data) {
  return request({
    url: api.workflow,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delWorkflow (id) {
  return request({
    url: api.workflow,
    method: 'delete',
    data: { id }
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

export function getRoleApi (parameter) {
  return request({
    url: api.roleApi,
    method: 'get',
    params: parameter
  })
}

export function saveRoleApi (data) {
  return request({
    url: api.roleApi,
    method: 'post',
    data: data
  })
}

export function getRoleDept (parameter) {
  return request({
    url: api.roleDept,
    method: 'get',
    params: parameter
  })
}

export function saveRoleDept (data) {
  return request({
    url: api.roleDept,
    method: 'post',
    data: data
  })
}

export function getRoleWorkflow (parameter) {
  return request({
    url: api.roleWorkflow,
    method: 'get',
    params: parameter
  })
}

export function saveRoleWorkflow (data) {
  return request({
    url: api.roleWorkflow,
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

export function getDept (parameter) {
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

//
export function getEquipmentUnit (params) {
  return request({
    url: api.equipmentUnit,
    method: 'get',
    params: params
  })
}

export function saveEquipmentUnit (data) {
  return request({
    url: api.equipmentUnit,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function deleteEquipmentUnit (id) {
  return request({
    url: api.equipmentUnit,
    method: 'delete',
    data: { id }
  })
}

// export function getWorkflowAuthority (parameter) {
//   return request({
//     url: api.WorkflowAuthority,
//     method: 'get',
//     params: parameter
//   })
// }

//
// export function getRoleWorkflowAuthority (parameter) {
//   return request({
//     url: api.roleWorkflowAuthority,
//     method: 'get',
//     params: parameter
//   })
// }

// export function saveRoleWorkflowAuthority (data) {
//   return request({
//     url: api.roleWorkflowAuthority,
//     method: 'post',
//     data: data
//   })
// }
