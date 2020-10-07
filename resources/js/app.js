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
import localeData from 'dayjs/plugin/localeData'
import localizedFormat from 'dayjs/plugin/localizedFormat'
import isoWeek from 'dayjs/plugin/isoWeek'
import weekday from 'dayjs/plugin/weekday'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import '~/plugins'
import '~/components'
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';
import * as GmapVue from 'gmap-vue';



// BootstrapVue
Vue.use(BootstrapVue)

// Vuelidate: for form validation
Vue.use(Vuelidate)

// Google Maps plugin
Vue.use(GmapVue, {
  load: {
    key: window.config.googleMaps,
    libraries: 'drawing',
    installComponents: true
  }
})

// VueLodash: for array manipulation
Vue.use(VueLodash, { lodash: lodash })

// Date/Time Picker
Vue.component('VueCtkDateTimePicker', VueCtkDateTimePicker);

Vue.config.productionTip = false
Vue.prototype.$userId = document.querySelector("meta[name='user_id']").getAttribute('content')



// Set moment options, locale, filters
Vue.prototype.$dayjs = dayjs
dayjs.extend(localeData)
dayjs.extend(localizedFormat)
dayjs.extend(isoWeek)
dayjs.extend(weekday)

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



/**
 * $CAN: permissions check prototype
 * @param { string, array } permission Accepts permission(s) to check as either a string or an array of strings
 * @return boolean Returns 'true' if user roles match any of the given permissions
 */ 
Vue.prototype.$can = function (permission) {
  var rolesAllowed = []
  var matchFound = false

  if (this.siteRoles !== null) {
    // Find which roles contain this permission
    this.siteRoles.forEach(item => {
      const sitePermissions = item.permissions
      const arrayPermissions = sitePermissions.map(t => t['name'])
      var foundRole = false

      if ((typeof permission) == 'string') {
        // If this role contains the given permission
        foundRole = arrayPermissions.indexOf(permission) >= 0
      } else if ((typeof permission) == 'object') {
        // If this role contains any of the given permissions
        for (let perm of permission) {
          foundRole = arrayPermissions.indexOf(perm) >= 0
          if (foundRole) { break }
        }
      }

      // If the role contains the permission(s), push it to the list of allowed roles for this resource
      if (foundRole) {
        var roleName = item.name
        rolesAllowed.push(roleName)
      }
    })


    // Now see if the user owns one of the roles above
    // If any of the users' roles match any of the allowed roles for this resource, return 'true'
    const teamRoles = this.team ? this.roles[this.team.id] : []
    const globalRoles = this.roles['global']
    const myRoles = teamRoles.concat(globalRoles)

    for (let myRole of myRoles) {
      matchFound = rolesAllowed.indexOf(myRole) >= 0
      if (matchFound) { break }
    }
  }

  return matchFound
}




window.bus = new Vue()

new Vue({
  i18n,
  store,
  router,
  vuetify,
  ...App
})
