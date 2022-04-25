/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-25 18:46:59
 */
import request from '@/utils/request'

const api = {
  dts: '/dts',
  dts_attachment: '/dts/attachment',
  //
  dts_progress: '/dts/progress',
  dts_ticket_handler: '/dts/ticket/handler',
  dts_ticket_toReview: '/dts/ticket/toReview'
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

export function delDts (data) {
  return request({
    url: api.dts,
    method: 'delete',
    data: data
  })
}

export function delAttachment (params) {
  return request({
    url: api.dts_attachment,
    method: 'delete',
    data: params
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
