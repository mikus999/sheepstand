import Vue from 'vue'
import App from '~/components/App'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import vuetify from '~/plugins/vuetify'
import VueLodash from 'vue-lodash'
import Vuelidate from 'vuelidate'
import lodash from 'lodash'
import dayjs from 'dayjs'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import '~/plugins'
import '~/components'
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';
import locales_de from 'dayjs/locale/de'

// BootstrapVue
Vue.use(BootstrapVue)

// Vuelidate: for form validation
Vue.use(Vuelidate)
Vue.use(VueLodash, { lodash: lodash })

// Date/Time Picker
Vue.component('VueCtkDateTimePicker', VueCtkDateTimePicker);

Vue.config.productionTip = false
Vue.prototype.$userId = document.querySelector("meta[name='user_id']").getAttribute('content')



// Set moment options, locale, filters
Vue.prototype.dayjs = dayjs
dayjs.locale(store.getters['lang/locale'])

Vue.filter('formatDate', function (value) {
  if (value) {
    return dayjs(String(value)).format('l')
  }
})

Vue.filter('formatTime', function (value) {
  if (value) {
    return dayjs(String(value)).format('LT')
  }
})

Vue.filter('formatDay', function (value) {
  if (value) {
    return dayjs(String(value)).format('ddd, ll')
  }
})

Vue.filter('formatWeekdayShort', function (value) {
  if (value) {
    return dayjs(String(value)).format('ddd')
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
