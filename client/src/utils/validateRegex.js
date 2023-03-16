/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-24 09:56:03
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-17 00:07:51
 */

// 正则表达式
export const USERNAME = {
  regex: /^([\u4e00-\u9fa5]){1,6}$/,
  msg: '请输入中文，不超过6个字'
}

export const PHONE = {
  regex: /^[1][3,4,5,7,8][0-9]{9}$/,
  msg: '请输入11位手机号码'
}

export const EMAIL = {
  regex: /^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/,
  msg: '请输入有效的电子邮箱'
}

export const ID_CARD = {
  regex: /^([1-9]\d{5}(18|19|20|(3\d))\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]){0,1}$/,
  msg: '请输入有效的身份证号码'
}

export const PASSWORD = {
  regex: /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z\W]{8,32}$/,
  msg: '密码最少8位，需包含字母，数字'
}

export const SMS_CODE = {
  regex: /^[1-9]\d{4}$/,
  msg: '请输入验证码'
}

// export const englishChineseLetter = {
//   regex: /^([a-zA-z\u4e00-\u9fa5]{0,40})$/u,
//   msg: '请输入中文或英文，不超过40个字'
// }

// export const lowerLetterUnderline = {
//   regex: /^[a-z_]{0,60}$/,
//   msg: '请输入小写字母或下划线，不超过60个字'
// }

// export const lowerLetterNumUnderline = {
//   regex: /^[a-z_0-9]{0,60}$/,
//   msg: '请输入小写字母，数字或下划线，不超过60个字'
// }

// export const positiveFloatNumber = {
//   regex: /^([1-9]([0-9]){0,}([.]([0-9]){0,}[1-9]+)?)?([0]([.]([0-9]){0,}[1-9]+)?)?$/,
//   msg: '请输入数字，例如0，12，12.3，0.123'
// }

export const TITLE = {
  regex: /^([\u4e00-\u9fa5a-zA-Z0-9_-]{0,64})$/u,
  msg: '请输入中文、英文、数字、横线或下划线'
}

export const KEY_WORDS = {
  regex: /^([\u4e00-\u9fa5a-zA-Z0-9，,]{0,64})$/u,
  msg: '请输入中文、英文或数字，多个关键词用逗号分隔'
}

export const TEXT = {
  regex: /^([\u4e00-\u9fa5a-zA-Z0-9,.?:;，。？：；_-]{0,1000})$/u,
  msg: '请输入中文、英文或数字，不包含特殊符号 @ # $ % & *" 等'
}

export const DOC_NUM = {
  regex: /^([a-zA-Z0-9-]{0,64})$/u,
  msg: '可输入英文、数字或横线'
}
