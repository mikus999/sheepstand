import Vue from 'vue'
import Snackbar from './Snackbar'
import PageTitle from './PageTitle'

// Components that are registered globaly.
[
  Snackbar,
  PageTitle
].forEach(Component => {
  Vue.component(Component.name, Component)
})
