import Vue from 'vue'
import App from '~/components/App'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import vuetify from '~/plugins/vuetify'
import Vuelidate from 'vuelidate'
import dayjs from 'dayjs'
import localeData from 'dayjs/plugin/localeData'
import localizedFormat from 'dayjs/plugin/localizedFormat'
import isoWeek from 'dayjs/plugin/isoWeek'
import weekday from 'dayjs/plugin/weekday'
import isBetween from 'dayjs/plugin/isBetween'
import '~/plugins'
import '~/components'
import VueClipboard from 'vue-clipboard2'
import 'typeface-roboto'


// VueClipboard
Vue.use(VueClipboard)

// Vuelidate: for form validation
Vue.use(Vuelidate)




Vue.config.productionTip = false
if (process.env.NODE_ENV === 'production') {
  Vue.config.devtools = false
  Vue.config.debug = false
  Vue.config.silent = true
}



// Set moment options, locale, filters
Vue.prototype.$dayjs = dayjs
dayjs.extend(localeData)
dayjs.extend(localizedFormat)
dayjs.extend(isoWeek)
dayjs.extend(weekday)
dayjs.extend(isBetween)

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

  if (this.siteRoles != null) {
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
    var myRoles = null
    const teamRoles = this.team ? this.roles[this.team.id] : null
    const globalRoles = this.roles['global']

    if (teamRoles) {
      myRoles = teamRoles.concat(globalRoles)
    } else {
      myRoles = globalRoles
    }

    for (let myRole of myRoles) {
      matchFound = rolesAllowed.indexOf(myRole) >= 0
      if (matchFound) { break }
    }
  }

  return matchFound
}


/**
 * $IS: roles check prototype
 * @param { string, array } roles Accepts role(s) to check as either a string or an array of strings
 * @return boolean Returns 'true' if user roles match any of the given roles
 */ 
Vue.prototype.$is = function (rolesAllowed) {
  var matchFound = false

  if (this.siteRoles != null) {

    // Now see if the user owns one of the roles above
    // If any of the users' roles match any of the allowed roles for this resource, return 'true'
    var myRoles = null
    const teamRoles = this.team ? this.roles[this.team.id] : null
    const globalRoles = this.roles['global']

    if (teamRoles) {
      myRoles = teamRoles.concat(globalRoles)
    } else {
      myRoles = globalRoles
    }

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
