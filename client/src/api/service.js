/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-08-28 10:43:58
 */
import request from '@/utils/request'

const api = {
  generator_event: '/generator/event',
  generator_event_statistic: '/generator/event/statistic',
  generator_event_export: '/generator/event/export',
  meters: '/meters'
}

// 机组事件
export function getGeneratorEvent (params) {
  return request({
    url: api.generator_event,
    method: 'get',
    params: params
  })
}

export function saveGeneratorEvent (data) {
  return request({
    url: api.generator_event,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function getGeneratorEventStatistic (params) {
  return request({
    url: api.generator_event_statistic,
    method: 'get',
    params: params
  })
}

export function delGeneratorEvent (params) {
  return request({
    url: api.generator_event,
    method: 'delete',
    data: params
  })
}

export function getExportGeneratorEvent (params) {
  return request({
    url: api.generator_event_export,
    method: 'get',
    responseType: 'blob',
    params: params
  })
}

// 电度表
export function saveMeterLogs (data) {
  return request({
    url: api.meters,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}
