/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-28 17:12:43
 */
import request from '@/utils/request'

const api = {
  dts: '/dts',
  dts_attachment: '/dts/attachment'
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

export function downloadAttachment (params) {
  return request({
    url: api.dts_attachment,
    method: 'get',
    responseType: 'blob',
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

export function updateDts (data) {
  return request({
    url: api.dts,
    method: 'put',
    data: data
  })
}
