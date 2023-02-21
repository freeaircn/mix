/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-24 09:56:03
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-21 16:25:39
 */

export default {
  /**
     * 头像
     */
  uploadAvatarApi: process.env.VUE_APP_API_BASE_URL + '/account/avatar',

  /**
     * DTS 工作流配置文件
     */
  dtsAttachmentMaxSize: 8388608, // 8*1024*1024
  dtsAttachmentMaxNumber: 5,
  dtsAttachmentFileTypes: [
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
  ],
  /**
     * 图纸库配置文件
     */
  drawing: {
    uploadUrl: '/api/drawing/file',
    maxFileSize: 104857600, // 100 1024 1024,
    maxFileNumber: 1,
    allowedFileTypes: [
      'application/zip',
      'application/pdf'
    ]
  }
}
