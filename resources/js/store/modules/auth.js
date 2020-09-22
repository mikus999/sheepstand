import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'

// state
export const state = {
  user: null,
  roles: null,
  token: Cookies.get('token'),
  isSuperAdmin: false
}

// getters
export const getters = {
  user: state => state.user,
  roles: state => state.roles,
  token: state => state.token,
  check: state => state.user !== null,
  isSuperAdmin: state => state.roles.indexOf('super_admin') >= 0
}

// mutations
export const mutations = {
  [types.SAVE_TOKEN] (state, { token, remember }) {
    state.token = token
    Cookies.set('token', token, { expires: remember ? 365 : null })
  },

  [types.FETCH_USER_SUCCESS] (state, { user }) {
    state.user = user
  },

  [types.FETCH_USER_FAILURE] (state) {
    state.token = null
    Cookies.remove('token')
  },

  [types.FETCH_ROLES] (state, { roles }) {
    state.roles = roles
  },

  [types.LOGOUT] (state) {
    state.user = null
    state.roles = null
    state.token = null

    Cookies.remove('token')
  },

  [types.UPDATE_USER] (state, { user }) {
    state.user = user
  }
}

// actions
export const actions = {
  saveToken ({ commit, dispatch }, payload) {
    commit(types.SAVE_TOKEN, payload)
  },

  fetchUser ({ commit }) {
    return new Promise((resolve, reject) => {
      axios.get('/api/user')
      .then(response => {
        const { data } = response
        commit(types.FETCH_USER_SUCCESS, { user: data })
        resolve()
      })
      .catch(error => {
        commit(types.FETCH_USER_FAILURE)
        reject()
      })
    })
  },

  fetchRoles ({ commit, rootState }) {
    return new Promise((resolve, reject) => {
      const teamid = rootState.teams.team.id

      axios({
        method: 'post',      
        url: '/api/user/roles/get',
        data: {
          team_id: teamid
        }
      })
      .then(response => {
        const roles = response.data.roles
        commit(types.FETCH_ROLES, { roles: roles })
        resolve()
      })
      .catch(error => {
        reject()
      })
    })
  },

  updateUser ({ commit }, payload) {
    commit(types.UPDATE_USER, payload)
  },

  async logout ({ commit }) {
    try {
      await axios.post('/api/logout')
    } catch (e) { }

    commit(types.LOGOUT)
  },

  async fetchOauthUrl (ctx, { provider }) {
    const { data } = await axios.post(`/api/oauth/${provider}`)

    return data.url
  }
}
