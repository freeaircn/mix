/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-24 09:56:03
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-28 23:30:55
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
  ]
}
