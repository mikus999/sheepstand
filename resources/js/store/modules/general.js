import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'

export const state = {
  message_count: null,
}

export const getters = {
  message_count: state => state.message_count,
}

export const mutations = {
  [types.SET_MESSAGE_COUNT] (state, payload) {
    state.message_count = payload
  },
}

export const actions = {
  init ({ dispatch }) {
    return Promise.all([
      dispatch('auth/fetchUser', null, {root: true}),
      dispatch('auth/fetchSiteRoles', null, {root: true}),
      dispatch('general/fetchMessageCounts', null, {root: true})
    ])
  },


  fetchMessageCounts ({ commit }) {
    return new Promise((resolve, reject) => {
      axios.get('/api/messages/count')
      .then(response => {
        commit(types.SET_MESSAGE_COUNT, response.data)

        resolve()
      })
    })
  },
}
