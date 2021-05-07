import * as types from '../mutation-types'

export const state = {
  content: '',
  color: '',
  persistent: false
}

export const mutations = {
  [types.SHOW_MESSAGE] (state, payload) {
    state.content = payload.content
    state.color = payload.color
    state.persistent = payload.persistent
  }
}