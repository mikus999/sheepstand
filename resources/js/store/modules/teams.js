import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'

export const state = {
  team: Cookies.getJSON('team') ? Cookies.getJSON('team') : null,
  teams: [],
  hasTeam: false
}

export const getters = {
  getTeam: state => state.team,
  getTeams: state => state.teams,
  hasTeam: state => state.hasTeam
}

export const mutations = {
  [types.FETCH_TEAMS] (state, payload) {
    state.teams = payload
  },

  [types.SET_TEAM] (state, payload) {
    state.team = payload
  },

  [types.SET_HASTEAM] (state, payload) {
    state.hasTeam = payload > 0
  },

  [types.LOGOUT_TEAMS] (state) {
    state.teams = null,
    state.team = null,
    state.hasTeam = false
  }
}

export const actions = {
  async fetchTeams ({ dispatch, commit }) {
    await axios.get('/api/teams')
      .then(response => {
        commit(types.FETCH_TEAMS, response.data.teams)
        commit(types.SET_HASTEAM, response.data.teams.length)

        if (state.team === null && state.hasTeam) {
          dispatch('setTeam', response.data.teams[0].id)
        }
      })
  },

  async setTeam ({ commit }, { teamid }) {
    if (teamid === undefined || teamid === null) {
      teamid = state.teams[0].id
    }
    
    if (teamid !== undefined) {
      await axios.get('/api/teams/' + teamid)
        .then(response => {
          Cookies.set('team', response.data, { expires: 365 })
          commit(types.SET_TEAM, response.data)
        })
    }
  },

  async logoutTeams ({ commit }) {
    commit(types.LOGOUT_TEAMS)
  }
}
