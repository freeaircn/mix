/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-12-14 16:27:10
 */
import request from '@/utils/request'

const api = {
  meters: '/meters',
  record_detail: '/meters/record/detail',
  report_daily: '/meters/report/daily',
  //
  statistic_chart_data: '/meters/statistic/chart/data',
  statistic_overall: 'meters/statistic/overall',
  //
  plan_deal: '/meters/plan_deal'
}

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
