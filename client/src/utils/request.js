import axios from 'axios'
import notification from 'ant-design-vue/es/notification'
import store from '@/store'
import { ACCESS_TOKEN } from '@/store/mutation-types'
import Cookies from 'js-cookie'
import { VueAxios } from './axios'
import qs from 'qs'

const request = axios.create({
  // API 请求的默认前缀
  baseURL: process.env.VUE_APP_API_BASE_URL,
  timeout: 6000 // 请求超时时间
})

// My code
request.defaults.headers.post['Content-Type'] = 'application/json'
request.defaults.headers.put['Content-Type'] = 'application/json'
request.defaults.headers.delete['Content-Type'] = 'application/json'

// My code: Post请求转换请求，使用json。qs 序列化，undefined或空数组，axios post 提交时，qs不填入http body。
request.defaults.transformRequest = [function (data) {
  // return qs.stringify(data, { arrayFormat: 'indices' })
  return JSON.stringify(data)
}]

// My code: Get请求，指定请求参数序列号方法
request.defaults.paramsSerializer = function (params) {
  return qs.stringify(params, { arrayFormat: 'indices' })
}

// 成功响应处理
const responseHandler = (response) => {
  if (response.data instanceof Blob) {
    return Promise.resolve(response)
  }
  console.log('--- server response ---', response.data)
  const res = response.data
  if (res.code) {
    if (res.code === 0) {
      if (typeof res.msg !== 'undefined') {
        notification.success({
          message: '成功',
          description: res.msg,
          duration: 3
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
          description: res.msg,
          duration: 3
        })
      }
      return Promise.reject(res.code)
    }
  } else {
    if (typeof res.msg !== 'undefined') {
      notification.success({
        message: '成功',
        description: res.msg,
        duration: 3
      })
    }
    if (typeof res.data !== 'undefined') {
      return Promise.resolve(res.data)
    } else {
      return Promise.resolve()
    }
  }
}

// 异常响应处理
const errorHandler = (error) => {
  console.log('--- failed response ---', error.response)
  if (error.response) {
    if (error.response.status === 400) {
      notification.error({
        message: '错误',
        description: error.response.data.messages.error,
        duration: 3
      })
    }
    //
    if (error.response.status === 401) {
      notification.error({
        message: '错误',
        description: error.response.data.messages.error,
        duration: 3
      })
      if (error.response.data.error === '1') {
        if (Cookies.get(ACCESS_TOKEN)) {
          store.dispatch('Logout').then(() => {
            setTimeout(() => {
              window.location.reload()
            }, 500)
          })
        }
      }
    }
    //
    if (error.response.status === 404) {
      notification.warning({
        message: '错误',
        description: error.response.data.messages.error,
        duration: 3
      })
    }
    //
    if (error.response.status === 429) {
      notification.warning({
        message: '警告',
        description: error.response.data.messages.error,
        duration: 3
      })
    }
    //
    if (error.response.status === 500) {
      notification.error({
        message: '错误',
        description: error.response.data.messages.error,
        duration: 3
      })
    }
  }

  return Promise.reject(error)
}

// My code: 响应拦截
request.interceptors.response.use(responseHandler, errorHandler)
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

const request2 = axios.create({
  // API 请求的默认前缀
  baseURL: process.env.VUE_APP_API_BASE_URL,
  timeout: 6000 // 请求超时时间
})

request2.interceptors.response.use(responseHandler, errorHandler)

export default request

export {
  installer as VueAxios,
  request as axios,
  request,
  request2
}
