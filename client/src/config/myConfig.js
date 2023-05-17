/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-24 09:56:03
 * @LastEditors: freeair
 * @LastEditTime: 2023-05-17 16:28:25
 */

/**
* 头像
*/
export const avatarApi = {
  upload: process.env.VUE_APP_API_BASE_URL + '/account/avatar'
}

/**
* 图纸库
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
* DTS 工作流
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

/**
* 党支部
*/
export const partyBranch = {
  uploadUrl: '/api/party_branch/file',
  maxFileSize: 104857600, // 100 1024 1024,
  maxFileNumber: 2,
  allowedFileTypes: [
    'application/zip',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
  ],
  allowedPreviewFileTypes: [
    'pdf'
  ],
  cardHeaderStyle: {
    color: '#ec1010',
    fontSize: '18px',
    fontWeight: '600'
  }
}
