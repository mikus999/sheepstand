import Vue from 'vue'
import Snackbar from './Snackbar'

// Components that are registered globaly.
[
  Snackbar
].forEach(Component => {
  Vue.component(Component.name, Component)
})
