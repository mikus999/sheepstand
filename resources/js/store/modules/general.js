import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'
import Vuetify from '~/plugins/vuetify'

export const state = {
  theme: Cookies.get('theme') || 'light',
  message_count: null,
}

export const getters = {
  theme: state => state.theme,
  message_count: state => state.message_count,
}

export const mutations = {
  [types.SET_THEME] (state, payload) {
    state.theme = payload
  },

  [types.SET_MESSAGE_COUNT] (state, payload) {
    state.message_count = payload
  },
}

export const actions = {
  init ({ dispatch }) {
    return Promise.all([
      dispatch('auth/fetchUser', null, {root: true}),
      dispatch('auth/fetchSiteRoles', null, {root: true}),
      dispatch('general/scheduledTasks', null, {root: true}),
    ])
  },


  scheduledTasks ({ commit }) {
    return new Promise((resolve, reject) => {

      // Get scheduled tasks
      axios.get('/api/tasks/scheduled')
      .then(response => {
        // Commit message counts for inbox
        commit(types.SET_MESSAGE_COUNT, response.data.data.message_count)

        resolve()
      })
    })
  },

  getTheme ({ dispatch }) {
    const currTheme = Cookies.get('theme') || 'light'
    dispatch('setTheme', currTheme)
  },

  setTheme ({ commit }, payload) {
    Vuetify.framework.theme.dark = (payload == 'dark')

    commit(types.SET_THEME, payload)

    Cookies.set('theme', payload, { expires: 365 })
  }
}
