/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-31 20:16:54
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

export function delGeneratorEvent (id) {
  return request({
    url: api.generator_event,
    method: 'delete',
    data: { id }
  })
}
