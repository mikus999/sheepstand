import { mapGetters, mapState } from 'vuex'
import axios from 'axios'
import { result } from 'lodash'


export const helper = {
  computed: {
    ...mapGetters({
      user: 'auth/user',
      roles: 'auth/roles',
      siteRoles: 'auth/siteRoles',
      isSuperAdmin: 'auth/isSuperAdmin',
      teams: 'auth/teams',
      team: 'auth/team',
      hasTeam: 'auth/hasTeam',
      locale: 'lang/locale',
      locales: 'lang/locales',
      theme: 'general/theme',
      message_count: 'general/message_count'
    }),

    isTeamOwner () {
      return this.team.user_id == this.user.id
    },

    notificationsEnabled () {
      var result = false
      if (this.team.notificationsettings != null && this.team.notificationsettings.telegram_channel_id != null) {
        result = true
      }
      return result
    },
  },

  data () {
    return {
      scheduleStatus: [
        { 
          // Shift planning
          value: 0,
          text: this.$t('schedules.status_0'), 
          text_user: this.$t('schedules.status_closed'),
          color: 'deep-orange'
        },
        { 
          // Assignments
          value: 1,
          text: this.$t('schedules.status_1'), 
          text_user: this.$t('schedules.status_open'),
          color: 'blue'
        },
        { 
          // Finalized
          value: 2,
          text: this.$t('schedules.status_2'), 
          text_user: this.$t('schedules.status_closed'),
          color: 'green'
        },
        { 
          // Archived
          value: 3,
          text: this.$t('schedules.status_3'), 
          text_user: this.$t('schedules.status_closed'),
          color: 'red'
        },
        { 
          // Unknown
          value: 4,
          text: this.$t('schedules.status_4'), 
          text_user: this.$t('schedules.status_closed'),
          color: 'grey'
        }
      ],

      shiftStatus: [
        { 
          value: 0,
          text: this.$t('shifts.status_0'), 
          color: 'deep-orange',
          icon: 'mdi-account-clock'
        },
        { 
          value: 1,
          text: this.$t('shifts.status_1'), 
          color: 'deep-orange',
          icon: 'mdi-account-clock'
        },
        { 
          value: 2,
          text: this.$t('shifts.status_2'), 
          color: 'green',
          icon: 'mdi-account-check'
        },
        { 
          value: 3,
          text: this.$t('shifts.status_3'), 
          color: 'red darken-4',
          icon: 'mdi-account-cancel'
        },
        { 
          value: 4,
          text: this.$t('shifts.status_4'), 
          color: 'blue',
          icon: 'mdi-account-convert'
        }
      ],

      templateStartDate: '2001-01-01', // MONDAY, JANUARY 1, 2001
    }
  },

  methods: {
    formatJSON (data) {
      if (data.name) {
        return JSON.parse(JSON.stringify(data))
      } else {
        return JSON.parse(data)
      }
    },

    formatHoursMinutes (minutes) {
      if (minutes >= 30) {
        var newHours = Math.floor(minutes / 60);          
        var newMinutes = minutes % 60;
        newMinutes = newMinutes.toString().padStart(2, 0);
        var newTime = newHours + ':' + newMinutes;
      } else {
        newTime = minutes
      }
      return newTime
    },

    async setTeam (team, route) {
      await this.$store.dispatch('auth/setTeam', team)
      await this.$store.dispatch('auth/fetchUser')

      if (route == undefined) {
        this.$router.go()
      } else if (route == 'self') {
        // Do nothing
      } else {
        this.$router.push({ name: route })
      }
    },

    async refreshStore () {
      await this.$store.dispatch('general/init')
    },

    async getTeams () {
      await this.$store.dispatch('auth/fetchUser')
    },

    async getRoles () {
      await this.$store.dispatch('auth/fetchUser')
    },

    setLocale (locale) {
      if (this.$i18n.locale !== locale) {
        this.$i18n.locale = locale
        this.$dayjs.locale(locale)
        this.$store.dispatch('lang/setLocale', { locale })
      }
    },

    async logout () {
      // Log out the user.
      await this.$store.dispatch('auth/logout')

      localStorage.removeItem('vuex');

      // Redirect to login.
      this.$router.push({ name: 'login' })
    },

    hasPermission (search) {
      var index = this.permissions.findIndex(function(perm, index) {
        if (perm.name == search)
          return true
      })

      return (index >= 0)
    },

    showSnackbar (content, color) {
      this.$store.commit('snackbar/SHOW_MESSAGE', { content, color })
    },

    copyText (content) {
      this.$copyText(content).then(e => {
        this.showSnackbar(this.$t('general.content_copied'), 'success')
      }, e => {
        alert('error')
      })
    },

    changeTheme(theme) {
      this.$store.dispatch('general/setTheme', theme)
    },

    getOS () {
      const userAgent = window.navigator.userAgent
      const platform = window.navigator.platform
      const macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K', 'darwin']
      const windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE']
      const iosPlatforms = ['iPhone', 'iPad', 'iPod']
      var os = null
      var link = null
      var osDetails = { 
        name: null, 
        icon: null
      }

          
      if (macosPlatforms.indexOf(platform) !== -1) {
        osDetails.name = 'macOS'
        osDetails.icon = 'mdi-apple'
      } else if (iosPlatforms.indexOf(platform) !== -1) {
        osDetails.name = 'iOS'
        osDetails.icon = 'mdi-apple'
      } else if (windowsPlatforms.indexOf(platform) !== -1) {
        osDetails.name = 'Windows'
        osDetails.icon = 'mdi-windows'
      } else if (/Android/.test(userAgent)) {
        osDetails.name = 'Android'
        osDetails.icon = 'mdi-android'
      } else if (!os && /Linux/.test(platform)) {
        osDetails.name = 'Linux'
        osDetails.icon = 'mdi-linux'
      } else {
        osDetails.name = 'Unknown'
        osDetails.icon = 'mdi-help-circle'
      }

      return osDetails;
    },

    getTelegramLink (os) {
      const dlLink_android = 'https://telegram.org/dl/android'
      const dlLink_ios = 'https://telegram.org/dl/ios'
      const dlLink_other = 'https://telegram.org/apps'
      const dlLink_desktop = 'https://desktop.telegram.org'
      const dlLink_macos = 'https://macos.telegram.org'

      switch (os) {
        case 'macOS':
          return dlLink_macos
        case 'Windows':
        case 'Linux':
          return dlLink_desktop
        case 'Android':
          return dlLink_android
        case 'iOS':
          return dlLink_ios
        default:
          return dlLink_other
      }
    }

  },

}



export const scheduling = {
  methods: {
    filterShiftsAvailability (shifts, user) {
      var avail_shifts = shifts.filter(shift => 
        shift.users.filter(u => u.id == user.id).length > 0 || // include shift if user is already assigned to it, regardless of availability
        this.checkShiftAvailability(shift, user)
      )
      return avail_shifts
    },

    filterUsersAvailability (shift, users) {
      var avail_users = users.filter(user => 
        shift.users.filter(u => u.id == user.id).length > 0 || // include user if he is already assigned, regardless of availability
        this.checkShiftAvailability(shift, user)
      )

      // Show selected users at top of list
      avail_users.sort( ( a, b) => {
        return shift.users.filter(u => u.id == b.id).length - shift.users.filter(u => u.id == a.id).length
      });
      return avail_users
    },

    checkShiftAvailability(shift, user) {
      // Day of Week: Monday = 1, Sunday = 7 (ISO standard)

      var result = true
      const availability = user.user_availabilities
      const vacations = user.user_vacations
      const shift_date = this.$dayjs(shift.time_start).format('YYYY-MM-DD')
      const shift_dow = this.$dayjs(shift.time_start).isoWeekday()
      const shift_start_hour = this.$dayjs(shift.time_start).hour()
      var shift_end_hour = this.$dayjs(shift.time_end).hour()

      if (this.$dayjs(shift.time_end).minute() > 0) {
        shift_end_hour += 1
      }


      // First, check shift against user's weekly availability schedule
      if (availability && availability.length > 0) {
        var check_start = availability.filter(a => 
          a.day_of_week == shift_dow && 
          shift_start_hour >= this.$dayjs('2001-01-01 ' + a.start_time).hour() &&
          shift_start_hour < this.$dayjs('2001-01-01 ' + a.end_time).hour() &&
          a.available == 1
        );

        var check_end = availability.filter(a => 
          a.day_of_week == shift_dow && 
          shift_end_hour > this.$dayjs('2001-01-01 ' + a.start_time).hour() &&
          shift_end_hour <= this.$dayjs('2001-01-01 ' + a.end_time).hour() &&
          a.available == 1
        );

        result = check_start.length > 0 && check_end.length > 0
      }


      // Next, if the shift fits user's weekly availability, check against their vacation schedule
      if (result && vacations && vacations.length > 0) {
        var check_vac = vacations.filter(v => 
          this.$dayjs(shift_date).isBetween(this.$dayjs(v.date_start), this.$dayjs(v.date_end), null, '[]')
        )

        result = check_vac.length == 0 // shift does not fall into any vacation date ranges
      }

      return result
    },


  }
}



export const messages = {
  methods: {
    message_trade_offer (publisher, time_start, time_end, location) {
      const lang = this.team.language || 'en'     

      var shiftDayTime = this.$dayjs(time_start).locale(lang).format('ddd, ll') 
      shiftDayTime += ' ' + this.$dayjs(time_start).locale(lang).format('LT') + ' - '
      shiftDayTime += ' ' + this.$dayjs(time_end).locale(lang).format('LT')


      var message = ''
      message += this.$t('system_messages.new_trade_offer')
      message += '\n\n' + this.$t('shifts.offered_by') + ": " + publisher
      message += '\n' + this.$t('shifts.shift_time') + ": " + shiftDayTime
      message += '\n' + this.$t('shifts.location') + ": " + location
      message = encodeURIComponent(message)
      
      return message
    }
  }
}


export default helper
