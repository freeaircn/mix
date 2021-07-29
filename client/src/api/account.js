/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-29 22:49:27
 */
import request from '@/utils/request'

export function apiGetUserInfo (params) {
  return request({
    url: '/account/info',
    method: 'get',
    params: params
  })
}

export function apiGetUserMenus (params) {
  return request({
    url: '/account/menus',
    method: 'get',
    params: params
  })
}

export function apiUpdateUserInfo (data) {
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
