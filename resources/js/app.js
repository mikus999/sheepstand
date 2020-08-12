import Vue from 'vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import vuetify from '~/plugins/vuetify'
import App from '~/components/App'
import moment from 'moment'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import '~/plugins'
import '~/components'

// Install BootstrapVue
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)

Vue.config.productionTip = false
Vue.prototype.$userId = document.querySelector("meta[name='user_id']").getAttribute('content')

Vue.filter('formatDate', function (value) {
  if (value) {
    return moment(String(value)).format('DD.MM.YYYY')
  }
})

window.bus = new Vue()

/* eslint-disable no-new */
new Vue({
  i18n,
  store,
  router,
  vuetify,
  ...App
})