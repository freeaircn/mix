/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-01 17:47:43
 */
import request from '@/utils/request'

const api = {
  meter: '/meter',
  plan_deal: '/meter/plan_deal'
}

export function apiQueryMeter (params) {
  return request({
    url: api.meter,
    method: 'get',
    params: params
  })
}

export function newRecord (data) {
  return request({
    url: api.meter,
    method: 'post',
    data: data
  })
}

export function updateRecord (data) {
  return request({
    url: api.meter,
    method: 'put',
    data: data
  })
}

export function delRecord (data) {
  return request({
    url: api.meter,
    method: 'delete',
    data: data
  })
}

export function updatePlanAndDealRecord (data) {
  return request({
    url: api.plan_deal,
    method: 'put',
    data: data
  })
}
