/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-29 21:28:50
 */
import request from '@/utils/request'

const api = {
  generator_event: '/generator/event'
}

// 机组事件
export function getGeneratorEvent (parameter) {
  return request({
    url: api.generator_event,
    method: 'get',
    params: parameter
  })
}

export function saveGeneratorEvent (data) {
  return request({
    url: api.generator_event,
    method: data.id && data.id > 0 ? 'put' : 'post',
    data: data
  })
}

export function delGeneratorEvent (id) {
  return request({
    url: api.generator_event,
    method: 'delete',
    data: { id }
  })
}
