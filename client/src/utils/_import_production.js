/*
 * @Description:
 * @Author: freeair
 * @Date: 2020-02-12 15:42:59
 * @LastEditors: freeair
 * @LastEditTime: 2021-06-29 13:26:34
 */
module.exports = file => () => import('@/views/' + file + '.vue')
