/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-07 23:18:31
 */
import request from '@/utils/request'

// const api = {
//   basic_setting_form: '/account/basic_setting/form'
// }

export function apiQueryAccount (params) {
  return request({
    url: '/account',
    method: 'get',
    params: params
  })
}

// export function apiGetUserInfo (params) {
//   return request({
//     url: '/account/info',
//     method: 'get',
//     params: params
//   })
// }

// export function getBasicSettingFormParam (params) {
//   return request({
//     url: api.basic_setting_form,
//     method: 'get',
//     params: params
//   })
// }

// export function apiGetUserMenus (params) {
//   return request({
//     url: '/account/menus',
//     method: 'get',
//     params: params
//   })
// }

export function apiUpdateUserBasicSetting (data) {
  return request({
    url: '/account',
    method: 'put',
    data: data
  })
}

export function apiUpdateLoginPassword (data) {
  return request({
    url: '/account/password',
    method: 'put',
    data: data
  })
}

export function apiUpdatePhone (data) {
  return request({
    url: '/account/phone',
    method: 'put',
    data: data
  })
}

export function apiUpdateEmail (data) {
  return request({
    url: '/account/email',
    method: 'put',
    data: data
  })
}

export function getSmsCaptcha (data) {
  return request({
    url: '/account/sms',
    method: 'put',
    data: data
  })
}
