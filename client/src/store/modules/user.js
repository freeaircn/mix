import Cookies from 'js-cookie'
import { login, logout } from '@/api/login'
import { apiQueryAccount, apiUpdateUserBasicSetting, apiUpdateLoginPassword, apiUpdatePhone, apiUpdateEmail } from '@/api/mix/account'
import { ACCESS_TOKEN } from '@/store/mutation-types'
import { resetRouter } from '@/router'

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
    },
    SET_USER_PHONE: (state, phone) => {
      if (state.info.phone) {
        state.info.phone = phone
      }
    },
    SET_USER_EMAIL: (state, email) => {
      if (state.info.email) {
        state.info.email = email
      }
    },
    SET_USER_AVATAR_FILE: (state, avatarFile) => {
      if (state.info.avatarFile) {
        state.info.avatarFile = avatarFile
      }
    }
  },

  actions: {
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
        logout(state.token)
          .then(() => {
            preLogout(commit)
            resolve()
          })
          .catch((error) => {
            preLogout(commit)
            reject(error)
          })
      })
    },

    GetUserResource ({ commit }) {
      return new Promise((resolve, reject) => {
        const params = { resource: 'session_and_menus' }
        apiQueryAccount(params)
          .then((res) => {
            const user = { ...res.session }
            commit('SET_INFO', user)
            if (user.username) {
              commit('SET_NAME', { name: user.username, welcome: '' })
            }
            if (user.avatarFile) {
              commit('SET_AVATAR', user.avatarFile)
            }
            //
            commit('SET_ROLES', ['VERIFIED'])
            // 用户授权页面
            resolve(res.menus)
        }).catch(error => {
          reject(error)
        })
      })
    },

    // 修改用户个人信息
    UpdateUserBasicSetting ({ commit }, newSetting) {
      return new Promise((resolve, reject) => {
        apiUpdateUserBasicSetting(newSetting)
          .then((data) => {
            if (data.session) {
              const user = { ...data.session }
              commit('SET_INFO', user)
              if (user.username) {
                commit('SET_NAME', { name: user.username, welcome: '' })
              }
              if (user.avatarFile) {
                commit('SET_AVATAR', user.avatarFile)
              }
            }
            resolve()
          }).catch((error) => {
            reject(error)
          })
      })
    },

    // 修改用户登录密码
    UpdateUserLoginPassword ({ commit }, params) {
      return new Promise((resolve, reject) => {
        apiUpdateLoginPassword(params).then(() => {
          resolve()
        }).catch((error) => {
          reject(error)
        })
      })
    },

    // 修改用户手机号
    UpdateUserPhone ({ commit }, params) {
      return new Promise((resolve, reject) => {
        apiUpdatePhone(params).then((data) => {
          // 存user数据
          if (data.phone) {
            commit('SET_USER_PHONE', data.phone)
          }
          resolve()
        }).catch((error) => {
          reject(error)
        })
      })
    },

    // 修改用户邮箱
    UpdateUserEmail ({ commit }, params) {
      return new Promise((resolve, reject) => {
        apiUpdateEmail(params).then((data) => {
          // 存user数据
          if (data.email) {
            commit('SET_USER_EMAIL', data.email)
          }
          resolve()
        }).catch((error) => {
          reject(error)
        })
      })
    },

    // 修改头像
    UpdateUserAvatar ({ commit }, params) {
      return new Promise((resolve, reject) => {
          if (params !== '') {
            commit('SET_USER_AVATAR_FILE', params)
            commit('SET_AVATAR', params)
            resolve()
          } else {
            reject(params)
          }
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
