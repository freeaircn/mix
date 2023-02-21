/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-21 10:05:19
 */
import request from '@/utils/request'

const api = {
  base_url: '/drawing',
  base_url_file: '/drawing/file'
}

export function apiQuery (params) {
  return request({
    url: api.base_url,
    method: 'get',
    params: params
  })
}

export function apiCreate (data) {
  return request({
    url: api.base_url,
    method: 'post',
    data: data
  })
}

export function apiUpdate (data) {
  return request({
    url: api.base_url,
    method: 'put',
    data: data
  })
}

export function apiDelete (data) {
  return request({
    url: api.base_url,
    method: 'delete',
    data: data
  })
}

export function apiDownloadFile (params) {
  return request({
    url: api.base_url_file,
    method: 'get',
    responseType: 'blob',
    params: params
  })
}

export function apiDeleteFile (data) {
  return request({
    url: api.base_url_file,
    method: 'delete',
    data: data
  })
}
