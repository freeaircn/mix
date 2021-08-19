/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-08-18 21:16:09
 */
import request from '@/utils/request'

const api = {
  generator_event: '/generator/event',
  generator_event_statistic: '/generator/event/statistic'
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
