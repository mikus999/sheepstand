import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'

// state
export const state = {
  user: null,
  roles: null,
  siteRoles: null,
  teams: [],
  team: null,
  token: Cookies.get('token'),
  tgUser: null, // Telegram user logged in
  tgGroup: null, // Telegram group for team
}

// getters
export const getters = {
  user: state => state.user,
  roles: state => state.roles,
  siteRoles: state => state.siteRoles,
  teams: state => state.teams,
  team: state => state.team,
  hasTeam: state => state.teams.length > 0,
  token: state => state.token,
  tgUser: state => state.tgUser,
  tgGroup: state => state.tgGroup,
  check: state => state.user !== null,
  isSuperAdmin: state => state.roles['global'].indexOf('super_admin') >= 0
}

// mutations
export const mutations = {
  [types.SAVE_TOKEN] (state, { token, remember }) {
    state.token = token
    Cookies.set('token', token, { expires: remember ? 365 : null })
  },

  [types.FETCH_USER_SUCCESS] (state, { user }) {
    state.user = user
    state.roles = user.roles
    state.teams = user.teams
  },

  [types.FETCH_USER_FAILURE] (state) {
    state.token = null
    Cookies.remove('token')
  },

  [types.FETCH_TEAMS] (state, payload) {
    state.teams = payload
  },

  [types.FETCH_SITEROLES] (state, { siteRoles }) {
    state.siteRoles = siteRoles
  },
  
  [types.LOGOUT] (state) {
    state.user = null
    state.team = null
    state.teams = null
    state.roles = null
    state.siteRoles = null
    state.token = null
    state.tgUser = null
    state.tgGroup = null

    Cookies.remove('token')
  },

  [types.UPDATE_USER] (state, { user }) {
    state.user = user
  },

  [types.SET_TEAM] (state, payload) {
    state.team = payload
  },

  [types.SET_TGUSER] (state, payload) {
    state.tgUser = payload
  },

  [types.SET_TGGROUP] (state, payload) {
    state.tgGroup = payload
  },
}

// actions
export const actions = {
  saveToken ({ commit, dispatch }, payload) {
    commit(types.SAVE_TOKEN, payload)
  },

  fetchUser ({ commit, dispatch }) {
    return new Promise((resolve, reject) => {
      axios.get('/api/user')
      .then(response => {
        const { data } = response
        commit(types.FETCH_USER_SUCCESS, { user: data })

        
        /**
         *  If the user is already a member of at least one team... 
         *    Check if one of his teams is marked as default. If so, call 'setTeam'
         *    If no default team is found, call 'setTeam' with first team in array.
         */
        const objTeams = data.teams
        var hasDefault = false

        if (objTeams.length > 0) {
          Object.keys(objTeams).forEach(function (item) {
            var objTeam = objTeams[item]

            if (objTeam.pivot.default_team === 1) {
              hasDefault = true
              dispatch('setTeam', objTeam)
            }
          });

          if (!hasDefault) {
            dispatch('setTeam', objTeams[0])
          }
        }

        resolve()
      })
      .catch(error => {
        commit(types.FETCH_USER_FAILURE)
        reject()
      })
    })
  },

  fetchSiteRoles ({ commit }) {
    return new Promise((resolve, reject) => {
      axios.get('/api/roles')
      .then(response => {
        commit(types.FETCH_SITEROLES, { siteRoles: response.data })

        resolve()
      })
    })
  },


  async setTeam ({ commit }, team) {  
    if (team !== undefined) {
      await axios({
        method: 'post',      
        url: '/api/teams/default/update',
        data: {
          teamid: team.id
        }
      })
      .then(response => {
        commit(types.SET_TEAM, team)
      })
    }
  },

  updateUser ({ commit }, payload) {
    commit(types.UPDATE_USER, payload)
  },

  updateTGUser ({ commit }, payload) {
    commit(types.SET_TGUSER, payload)
  },

  updateTGGroup ({ commit }, payload) {
    commit(types.SET_TGGROUP, payload)
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
