// import storage from 'store'
import Cookies from 'js-cookie'
import { login, getInfo, logout } from '@/api/login'
import { ACCESS_TOKEN } from '@/store/mutation-types'
import { resetRouter } from '@/router'
// import { welcome } from '@/utils/util'

const user = {
  state: {
    token: '',
    name: '',
    welcome: '',
    avatar: '',
    roles: [],
    info: {}
  },

  mutations: {
    SET_TOKEN: (state, token) => {
      state.token = token
    },
    SET_NAME: (state, { name, welcome }) => {
      state.name = name
      state.welcome = welcome
    },
    SET_AVATAR: (state, avatar) => {
      state.avatar = avatar
    },
    SET_ROLES: (state, roles) => {
      state.roles = roles
    },
    SET_INFO: (state, info) => {
      state.info = info
    }
  },

  actions: {
    // 登录
    // Login ({ commit }, userInfo) {
    //   return new Promise((resolve, reject) => {
    //     login(userInfo).then(response => {
    //       const result = response.result
    //       storage.set(ACCESS_TOKEN, result.token, 7 * 24 * 60 * 60 * 1000)
    //       commit('SET_TOKEN', result.token)
    //       resolve()
    //     }).catch(error => {
    //       reject(error)
    //     })
    //   })
    // },

    // 获取用户信息
    // GetInfo ({ commit }) {
    //   return new Promise((resolve, reject) => {
    //     getInfo().then(response => {
    //       const result = response.result

    //       if (result.role && result.role.permissions.length > 0) {
    //         const role = result.role
    //         role.permissions = result.role.permissions
    //         role.permissions.map(per => {
    //           if (per.actionEntitySet != null && per.actionEntitySet.length > 0) {
    //             const action = per.actionEntitySet.map(action => { return action.action })
    //             per.actionList = action
    //           }
    //         })
    //         role.permissionList = role.permissions.map(permission => { return permission.permissionId })
    //         commit('SET_ROLES', result.role)
    //         commit('SET_INFO', result)
    //       } else {
    //         reject(new Error('getInfo: roles must be a non-null array !'))
    //       }

    //       commit('SET_NAME', { name: result.name, welcome: welcome() })
    //       commit('SET_AVATAR', result.avatar)

    //       resolve(response)
    //     }).catch(error => {
    //       reject(error)
    //     })
    //   })
    // },

    // 登出
    // Logout ({ commit, state }) {
    //   return new Promise((resolve) => {
    //     logout(state.token).then(() => {
    //       commit('SET_TOKEN', '')
    //       commit('SET_ROLES', [])
    //       storage.remove(ACCESS_TOKEN)
    //       resolve()
    //     }).catch(() => {
    //       resolve()
    //     }).finally(() => {
    //     })
    //   })
    // },

    // Mix code
    Login ({ commit }, userInfo) {
      return new Promise((resolve, reject) => {
        login(userInfo).then(data => {
          // 写cookie
          if (data.token) {
            Cookies.set(ACCESS_TOKEN, data.token)
            commit('SET_TOKEN', data.token)
          }
          //
          resolve()
        }).catch(error => {
          reject(error)
        })
      })
    },

    Logout ({ commit, state }) {
      return new Promise((resolve, reject) => {
        logout(state.token).then(() => {
          preLogout(commit)
          resolve()
        }).catch((error) => {
          preLogout(commit)
          reject(error)
        })
      })
    },

    // 获取用户信息
    GetInfo ({ commit }) {
      return new Promise((resolve, reject) => {
        getInfo().then(data => {
          // 存user数据
          if (data.info) {
            const user = { ...data.info }
            commit('SET_INFO', user)
            if (user.username) {
              commit('SET_NAME', { name: user.username, welcome: '' })
            }
            if (user.avatarFile) {
              commit('SET_AVATAR', user.avatarFile)
            }
          }
          //
          commit('SET_ROLES', ['VERIFIED'])
          //
          if (data.menus) {
            resolve(data.menus)
          } else {
            resolve([])
          }
        }).catch(error => {
          reject(error)
        })
      })
    }
  }
}

export const preLogout = (commit) => {
  commit('SET_TOKEN', '')
  commit('SET_ROLES', [])
  commit('SET_NAME', { name: '', welcome: '' })
  commit('SET_AVATAR', '')
  commit('SET_INFO', {})
  Cookies.remove(ACCESS_TOKEN)
  resetRouter()
}

export default user
