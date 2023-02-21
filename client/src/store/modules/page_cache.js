/*
 * @Description:
 * @Author: freeair
 * @Date: 2022-05-28 23:24:20
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-21 09:53:35
 */

const pageCache = {
  state: {
    dtsListSearchParam: null,
    drawingListSearch: null
  },

  mutations: {
    SET_DTS_LIST_SEARCH_PARAM: (state, param) => {
      state.dtsListSearchParam = param
    },
    SET_DRAWING_LIST_SEARCH_PARAM: (state, param) => {
      state.drawingListSearch = param
    }
  },

  actions: {
    setDtsListSearchParam ({ commit }, param) {
      commit('SET_DTS_LIST_SEARCH_PARAM', param)
    },
    setDrawingListSearchParam ({ commit }, param) {
      commit('SET_DRAWING_LIST_SEARCH_PARAM', param)
    }
  }
}

export default pageCache
