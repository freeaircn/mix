/*
 * @Description:
 * @Author: freeair
 * @Date: 2022-05-28 23:24:20
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-28 23:40:36
 */

const pageCache = {
  state: {
    dtsListSearchParam: null
  },

  mutations: {
    SET_DTS_LIST_SEARCH_PARAM: (state, param) => {
      state.dtsListSearchParam = param
    }
  },

  actions: {
    setDtsListSearchParam ({ commit }, param) {
      commit('SET_DTS_LIST_SEARCH_PARAM', param)
    }
  }
}

export default pageCache
