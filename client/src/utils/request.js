import axios from 'axios'
import store from '@/store'
import storage from 'store'
import notification from 'ant-design-vue/es/notification'
import { VueAxios } from './axios'
import { ACCESS_TOKEN } from '@/store/mutation-types'
import qs from 'qs'

// 创建 axios 实例
const request = axios.create({
  // API 请求的默认前缀
  baseURL: process.env.VUE_APP_API_BASE_URL,
  timeout: 6000 // 请求超时时间
})

// 异常拦截处理器
const errorHandler = (error) => {
  if (error.response) {
    const data = error.response.data
    // 从 localstorage 获取 token
    const token = storage.get(ACCESS_TOKEN)
    if (error.response.status === 403) {
      notification.error({
        message: 'Forbidden',
        description: data.message
      })
    }
    if (error.response.status === 401 && !(data.result && data.result.isLogin)) {
      notification.error({
        message: 'Unauthorized',
        description: 'Authorization verification failed'
      })
      if (token) {
        store.dispatch('Logout').then(() => {
          setTimeout(() => {
            window.location.reload()
          }, 1500)
        })
      }
    }
  }
  return Promise.reject(error)
}

// request interceptor
// request.interceptors.request.use(config => {
//   const token = storage.get(ACCESS_TOKEN)
//   // 如果 token 存在
//   // 让每个请求携带自定义 token 请根据实际情况自行修改
//   if (token) {
//     config.headers['Access-Token'] = token
//   }
//   return config
// }, errorHandler)

// response interceptor
// request.interceptors.response.use((response) => {
//   return response.data
// }, errorHandler)

// Mix code
request.defaults.headers.post['Content-Type'] = 'application/json'
request.defaults.headers.put['Content-Type'] = 'application/json'
request.defaults.headers.delete['Content-Type'] = 'application/json'

// Mix code: Post请求转换请求，使用json。qs 序列化，undefined或空数组，axios post 提交时，qs不填入http body。
request.defaults.transformRequest = [function (data) {
  // return qs.stringify(data, { arrayFormat: 'indices' })
  return JSON.stringify(data)
}]

// Mix code: Get请求，指定请求参数序列号方法
request.defaults.paramsSerializer = function (params) {
  return qs.stringify(params, { arrayFormat: 'indices' })
}

// Mix code: 响应拦截
request.interceptors.response.use(
  // Any status code that lie within the range of 2xx cause this function to trigger
  response => {
    if (response.data instanceof Blob) {
      return Promise.resolve(response)
    }
    console.log('--- server response ---', response.data)
    const res = response.data
    if (res.code === 0) {
      if (typeof res.msg !== 'undefined') {
        notification.success({
          message: '成功',
          description: res.msg
        })
      }
      if (typeof res.data !== 'undefined') {
        return Promise.resolve(res.data)
      } else {
        return Promise.resolve()
      }
    } else {
      if (typeof res.msg !== 'undefined') {
        notification.warning({
          message: '失败',
          description: res.msg
        })
      }
      return Promise.reject(res.code)
    }
  }, errorHandler)
  // Any status codes that falls outside the range of 2xx cause this function to trigger
//   error => {
//     let code = 0
//     try {
//       code = error.response.data.status
//     } catch (e) {
//       if (error.toString().indexOf('Error: timeout') !== -1) {
//         notification.warning({
//           message: '失败',
//           description: '请求超时'
//         })
//         return Promise.reject(error)
//       }
//       if (error.toString().indexOf('Error: Network Error') !== -1) {
//         notification.warning({
//           message: '失败',
//           description: '网络错误'
//         })
//         return Promise.reject(error)
//       }
//     }
//     switch (code) {
//       case 401:
//         console.log('--- server response ---')
//         console.log('401登录状态已过期')
//         break
//       case 403:
//         console.log('--- server response ---')
//         console.log('403')
//         break
//       default:
//         console.log('--- server response ---')
//         console.log('其他错误')
//     }
//     return Promise.reject(error)
//   }
// )

const installer = {
  vm: {},
  install (Vue) {
    Vue.use(VueAxios, request)
  }
}

export default request

export {
  installer as VueAxios,
  request as axios
}
