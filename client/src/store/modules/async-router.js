/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2021-06-30 19:35:42
 */
/**
 * 向后端请求用户的菜单，动态生成路由
 */
import { constantRouterMap } from '@/config/router.config'
// import { generatorDynamicRouter } from '@/router/generator-routers'
import { buildDynamicRouter } from '@/router/generator-routers'

const permission = {
  state: {
    routers: constantRouterMap,
    addRouters: []
  },
  mutations: {
    SET_ROUTERS: (state, routers) => {
      state.addRouters = routers
      state.routers = constantRouterMap.concat(routers)
    }
  },
  actions: {
    // GenerateRoutes ({ commit }, data) {
    //   return new Promise(resolve => {
    //     const { token } = data
    //     generatorDynamicRouter(token).then(routers => {
    //       commit('SET_ROUTERS', routers)
    //       resolve()
    //     })
    //   })
    // }

    // My code
    GenerateRoutes ({ commit }, data) {
      return new Promise(resolve => {
        const { menus } = data
        const routers = buildDynamicRouter(menus)
        commit('SET_ROUTERS', routers)
        resolve()
      })
    },

    ClearRoutes ({ commit }) {
      return new Promise(resolve => {
        commit('SET_ROUTERS', [])
        resolve()
      })
    }
  }
}

export default permission
