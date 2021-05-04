import * as types from '../mutation-types'

export const state = {
  schedule: null,
  shifts: null,
  user_shifts: null,
  shift_users: null,
  shifts_available: null,
  shift_conflicts: null,
  trades: null,
  team_users: null,
  assignment_trade: null,
}


export const getters = {
  schedule: state => state.schedule,
  shifts: state => state.shifts,
  user_shifts: state => state.user_shifts,
  shift_users: state => state.shift_users,
  shifts_available: state => state.shifts_available,
  shift_conflicts: state => state.shift_conflicts,
  trades: state => state.trades,
  team_users: state => state.team_users,
  assignment_trade: state => state.assignment_trade
}


export const mutations = {
  [types.SET_SCHEDULE] (state, payload) {
    state.schedule = payload
  },

  [types.SET_SHIFTS] (state, payload) {
    state.shifts = payload
  },

  [types.SET_USER_SHIFTS] (state, payload) {
    state.user_shifts = payload
  },

  [types.SET_SHIFT_USERS] (state, payload) {
    state.shift_users = payload
  },

  [types.SET_SHIFTS_AVAILABLE] (state, payload) {
    state.shifts_available = payload
  },

  [types.SET_SHIFT_CONFLICTS] (state, payload) {
    state.shift_conflicts = payload
  },

  [types.SET_TRADES] (state, payload) {
    state.trades = payload
  },

  [types.SET_TEAM_USERS] (state, payload) {
    state.team_users = payload
  },

  [types.SET_ASSIGNMENT_TRADE] (state, payload) {
    state.assignment_trade = payload
  },

  [types.LOGOUT] (state) {
    state.schedule = null
    state.shifts = null
    state.user_shifts = null
    state.shift_users = null
    state.shifts_available = null
    state.shift_conflicts = null
    state.trades = null
    state.team_users = null
  },
}

export const actions = {
  async clear ({ commit }) {
    commit(types.LOGOUT)
  },
}