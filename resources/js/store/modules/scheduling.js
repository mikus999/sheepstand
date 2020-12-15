import * as types from '../mutation-types'

export const state = {
  schedule: null,
  shifts: null,
  user_shifts: null,
  shifts_available: null,
  shift_conflicts: null,
}


export const getters = {
  schedule: state => state.schedule,
  shifts: state => state.shifts,
  user_shifts: state => state.user_shifts,
  shifts_available: state => state.shifts_available,
  shift_conflicts: state => state.shift_conflicts
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

  [types.SET_SHIFTS_AVAILABLE] (state, payload) {
    state.shifts_available = payload
  },

  [types.SET_SHIFT_CONFLICTS] (state, payload) {
    state.shift_conflicts = payload
  }
}