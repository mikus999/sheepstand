import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'

export const state = {
  team: Cookies.get('team'),
  teams: [],
  hasTeam: false
}

export const getters = {
  getTeam: state => state.team.name ? JSON.parse(JSON.stringify(state.team)) : JSON.parse(state.team),
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
  }
}

export const actions = {
  async fetchTeams ({ commit }) {
    await axios.get('/api/teams')
      .then(response => {
        commit(types.FETCH_TEAMS, response.data.teams)
        commit(types.SET_HASTEAM, response.data.teams.length)

        if (state.team == null) {
          commit(types.SET_TEAM, response.data.teams[0])
        }
      })
  },

  async setTeam ({ commit }, { teamid }) {
    await axios.get('/api/teams/' + teamid)
      .then(response => {
        Cookies.set('team', response.data, { expires: 365 })
        commit(types.SET_TEAM, response.data)
      })
  }
}
