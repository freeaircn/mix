/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-01 00:11:13
 */
import request from '@/utils/request'

const api = {
  meter: '/meter',
  //
  meters: '/meters',
  record_detail: '/meters/record/detail',
  report_daily: '/meters/report/daily',
  //
  statistic_chart_data: '/meters/statistic/chart/data',
  statistic_overall: 'meters/statistic/overall',
  //
  plan_deal: '/meters/plan_deal'
}

// 2022-5-1
export function apiQueryMeter (params) {
  return request({
    url: api.meter,
    method: 'get',
    params: params
  })
}
// 2022-5-1

export function newRecord (data) {
  return request({
    url: api.meters,
    method: 'post',
    data: data
  })
}

export function updateRecord (data) {
  return request({
    url: api.meters,
    method: 'put',
    data: data
  })
}

export function getRecord (params) {
  return request({
    url: api.meters,
    method: 'get',
    params: params
  })
}

export function delRecord (data) {
  return request({
    url: api.meters,
    method: 'delete',
    data: data
  })
}

export function getRecordDetail (params) {
  return request({
    url: api.record_detail,
    method: 'get',
    params: params
  })
}

export function getReportDaily (params) {
  return request({
    url: api.report_daily,
    method: 'get',
    params: params
  })
}

export function getStatisticChartData (params) {
  return request({
    url: api.statistic_chart_data,
    method: 'get',
    params: params
  })
}

export function getStatisticOverall (params) {
  return request({
    url: api.statistic_overall,
    method: 'get',
    params: params
  })
}

export function getPlanAndDeal (params) {
  return request({
    url: api.plan_deal,
    method: 'get',
    params: params
  })
}

export function updatePlanAndDealRecord (data) {
  return request({
    url: api.plan_deal,
    method: 'put',
    data: data
  })
}
