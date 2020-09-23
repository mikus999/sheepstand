import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'

export const state = {
}

export const getters = {
}

export const mutations = {
}

export const actions = {
  init ({ dispatch }) {
    return Promise.all([
      dispatch('auth/fetchUser', null, {root: true}),
      dispatch('teams/fetchTeams', null, {root: true}),
      //dispatch('auth/fetchRoles', null, {root: true})
    ])
  }
}
