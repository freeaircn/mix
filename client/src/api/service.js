/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-02 19:35:53
 */
import request from '@/utils/request'

const api = {
  generator_event: '/generator/event',
  generator_event_statistic: '/generator/event/statistic',
  generator_event_export: '/generator/event/export',
  meters: '/meters',
  meters_log_detail: '/meters/log_detail',
  meters_daily_report: '/meters/daily_report',
  meters_basic_statistic: '/meters/basic_statistic',
  meters_overall_statistic: 'meters/overall_statistic',
  planning_kWh: '/meters/planning_kWh',
  dts_progress_template: '/dts/progress/template',
  dts_handler: '/dts/handler',
  dts_draft: '/dts/draft',
  dts_list: '/dts/list',
  dts_ticket_details: '/dts/ticket/details'
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

export function getMeterLogs (params) {
  return request({
    url: api.meters,
    method: 'get',
    params: params
  })
}

export function getMetersLogDetail (params) {
  return request({
    url: api.meters_log_detail,
    method: 'get',
    params: params
  })
}

export function delMeterLogs (data) {
  return request({
    url: api.meters,
    method: 'delete',
    data: data
  })
}

export function getMetersDailyReport (params) {
  return request({
    url: api.meters_daily_report,
    method: 'get',
    params: params
  })
}

export function getMetersBasicStatistic (params) {
  return request({
    url: api.meters_basic_statistic,
    method: 'get',
    params: params
  })
}

export function getPlanningKWh (params) {
  return request({
    url: api.planning_kWh,
    method: 'get',
    params: params
  })
}

export function updatePlanningKWhRecord (data) {
  return request({
    url: api.planning_kWh,
    method: 'put',
    data: data
  })
}

export function getMetersOverallStatistic (params) {
  return request({
    url: api.meters_overall_statistic,
    method: 'get',
    params: params
  })
}

// Dts
export function getDtsProgressTemplate (params) {
  return request({
    url: api.dts_progress_template,
    method: 'get',
    params: params
  })
}

export function getDtsHandler (parameter) {
  return request({
    url: api.dts_handler,
    method: 'get',
    params: parameter
  })
}

export function postDtsDraft (data) {
  return request({
    url: api.dts_draft,
    method: 'post',
    data: data
  })
}

export function getDtsList (params) {
  return request({
    url: api.dts_list,
    method: 'get',
    params: params
  })
}

export function getDtsTicketDetails (params) {
  return request({
    url: api.dts_ticket_details,
    method: 'get',
    params: params
  })
}
