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
    ])
  }
}
