/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-18 23:10:12
 */
import { request, request2 } from '@/utils/request'

const api = {
  base_url: '/party_branch',
  base_url_file: '/party_branch/file'
}

export function apiQuery (params) {
  return request({
    url: api.base_url,
    method: 'get',
    params: params
  })
}

export function apiCreate (data) {
  return request2({
    url: api.base_url,
    method: 'post',
    data: data
  })
}

export function apiUpload (data) {
  return request2({
    url: api.base_url_file,
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
