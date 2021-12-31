/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-12-31 20:03:34
 */
import request from '@/utils/request'

const api = {
  generator_event: '/generator/event',
  generator_event_statistic: '/generator/event/statistic',
  generator_event_export: '/generator/event/export',
  generator_event_sync_kkx: '/generator/event/sync/kkx'
}

// 机组事件
export function apiGetEvent (params) {
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

export function apiGetEventStatisticChartData (params) {
  return request({
    url: api.generator_event_statistic,
    method: 'get',
    params: params
  })
}

export function apiDelEvent (params) {
  return request({
    url: api.generator_event,
    method: 'delete',
    data: params
  })
}

export function apiGetExportEvent (params) {
  return request({
    url: api.generator_event_export,
    method: 'get',
    responseType: 'blob',
    params: params
  })
}

export function apiSyncToKKX (params) {
  return request({
    url: api.generator_event_sync_kkx,
    method: 'get',
    params: params,
    timeout: 15000
  })
}
