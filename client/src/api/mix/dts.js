/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-24 23:17:31
 */
import request from '@/utils/request'

const api = {
  dts: '/dts',
  dts_query_params: '/dts/query_params',
  dts_blank_form: '/dts/blank_form',
  dts_device_list: '/dts/device_list',
  dts_list: '/dts/list',
  //
  dts_attachment: '/dts/attachment',
  //
  dts_details: '/dts/details',
  dts_progress: '/dts/progress',
  dts_ticket_handler: '/dts/ticket/handler',
  dts_ticket_toReview: '/dts/ticket/toReview'
}

// DTS
export function getBlankForm (params) {
  return request({
    url: api.dts_blank_form,
    method: 'get',
    params: params
  })
}

export function queryDts (params) {
  return request({
    url: api.dts,
    method: 'get',
    params: params
  })
}

export function createDts (data) {
  return request({
    url: api.dts,
    method: 'post',
    data: data
  })
}

export function getQueryParams (params) {
  return request({
    url: api.dts_query_params,
    method: 'get',
    params: params
  })
}

export function getList (params) {
  return request({
    url: api.dts_list,
    method: 'get',
    params: params
  })
}

export function delAttachment (params) {
  return request({
    url: api.dts_attachment,
    method: 'delete',
    data: params
  })
}

export function getDeviceList (params) {
  return request({
    url: api.dts_device_list,
    method: 'get',
    params: params
  })
}

export function getDetails (params) {
  return request({
    url: api.dts_details,
    method: 'get',
    params: params
  })
}

export function putProgress (data) {
  return request({
    url: api.dts_progress,
    method: 'put',
    data: data
  })
}

export function putDtsTicketHandler (data) {
  return request({
    url: api.dts_ticket_handler,
    method: 'put',
    data: data
  })
}

export function postDtsTicketToReview (data) {
  return request({
    url: api.dts_ticket_toReview,
    method: 'post',
    data: data
  })
}
