/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-19 12:28:13
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-28 23:34:47
 */
import Vue from 'vue'
import Vuex from 'vuex'

import app from './modules/app'
import user from './modules/user'
import pageCache from './modules/page_cache'

// default router permission control
// import permission from './modules/permission'
import permission from './modules/async-router'

// dynamic router permission control (Experimental)
// import permission from './modules/async-router'
import getters from './getters'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    app,
    user,
    permission,
    pageCache
  },
  state: {

  },
  mutations: {

  },
  actions: {

  },
  getters
})
