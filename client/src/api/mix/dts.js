/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-07 22:26:57
 */
import request from '@/utils/request'

const api = {
  dts_blank_form: '/dts/blank_form',
  dts_draft: '/dts/draft',
  dts_list: '/dts/list',
  //
  dts_details: '/dts/details',
  dts_progress: '/dts/progress',
  dts_ticket_handler: '/dts/ticket/handler',
  dts_ticket_toReview: '/dts/ticket/toReview'
}

// DTS
export function postDraft (data) {
  return request({
    url: api.dts_draft,
    method: 'post',
    data: data
  })
}

export function getList (params) {
  return request({
    url: api.dts_list,
    method: 'get',
    params: params
  })
}

export function getBlankForm (params) {
  return request({
    url: api.dts_blank_form,
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
