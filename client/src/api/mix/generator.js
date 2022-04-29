/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-30 00:45:22
 */
import request from '@/utils/request'

const api = {
  generator_event: '/generator/event'
}

export function apiQueryEvent (params) {
  return request({
    url: api.generator_event,
    method: 'get',
    params: params
  })
}

export function apiSaveEvent (data) {
  return request({
    url: api.generator_event,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function apiDelEvent (data) {
  return request({
    url: api.generator_event,
    method: 'delete',
    data: data
  })
}

export function apiExportExcel (params) {
  return request({
    url: api.generator_event,
    method: 'get',
    responseType: 'blob',
    params: params
  })
}

export function apiSyncToKKX (params) {
  return request({
    url: api.generator_event,
    method: 'get',
    params: params,
    timeout: 15000
  })
}
