/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-12-15 11:02:12
 */
import request from '@/utils/request'

const api = {
  dts_draft: '/dts/draft',
  dts_list: '/dts/list',
  dts_ticket_blank_form: '/dts/ticket/blank_form',
  dts_ticket_details: '/dts/ticket/details',
  dts_ticket_progress: '/dts/ticket/progress',
  dts_ticket_handler: '/dts/ticket/handler',
  dts_ticket_toReview: '/dts/ticket/toReview'
}

// DTS
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

export function getDtsTicketBlankForm (params) {
  return request({
    url: api.dts_ticket_blank_form,
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

export function putDtsTicketProgress (data) {
  return request({
    url: api.dts_ticket_progress,
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
