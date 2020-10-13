import Vue from 'vue'
import Snackbar from './Snackbar'
import ConfirmBox from './ConfirmBox'
import PageTitle from './PageTitle'

// Components that are registered globaly.
[
  Snackbar,
  PageTitle,
  ConfirmBox
].forEach(Component => {
  Vue.component(Component.name, Component)
})
