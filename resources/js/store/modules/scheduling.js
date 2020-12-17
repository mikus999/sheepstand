import * as types from '../mutation-types'

export const state = {
  schedule: null,
  shifts: null,
  user_shifts: null,
  shift_users: null,
  shifts_available: null,
  shift_conflicts: null,
  trades: null
}


export const getters = {
  schedule: state => state.schedule,
  shifts: state => state.shifts,
  user_shifts: state => state.user_shifts,
  shift_users: state => state.shift_users,
  shifts_available: state => state.shifts_available,
  shift_conflicts: state => state.shift_conflicts,
  trades: state => state.trades
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
  }
}