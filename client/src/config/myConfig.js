/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-24 09:56:03
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-24 20:14:39
 */

/**
* 头像
*/
export const avatarApi = {
  uploadAvatarApi: process.env.VUE_APP_API_BASE_URL + '/account/avatar'
}

/**
* 图纸库 配置文件
*/
export const drawing = {
  uploadUrl: '/api/drawing/file',
  maxFileSize: 104857600, // 100 1024 1024,
  maxFileNumber: 1,
  allowedFileTypes: [
    'application/zip',
    'application/pdf'
  ],
  allowedPreviewFileTypes: [
    'pdf'
  ]
}

/**
* DTS 工作流配置文件
*/
export const dts = {
  maxFileSize: 8388608, // 8*1024*1024
  maxFileNumber: 5,
  allowedFileTypes: [
    'image/jpeg',
    'image/png',
    'text/plain',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/zip',
    'application/pdf'
  ]
}
