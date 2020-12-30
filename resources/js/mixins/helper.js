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
          icon: 'mdi-account-question'
        },
        { 
          value: 1,
          text: this.$t('shifts.status_1'), 
          color: 'grey',
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
          color: 'grey',
          icon: 'mdi-account-cancel'
        },
        { 
          value: 4,
          text: this.$t('shifts.status_4'), 
          color: 'blue',
          icon: 'mdi-account-switch'
        },
        { 
          value: 5,
          text: this.$t('shifts.status_4'), 
          color: 'blue',
          icon: 'mdi-account-switch'
        }
      ],

      ftsStatus: [
        {
          value: 0,
          text: '---'
        },
        {
          value: 1,
          text: this.$t('account.fts_pioneer')
        },
        {
          value: 2,
          text: this.$t('account.fts_sfts')
        },
        {
          value: 3,
          text: this.$t('account.fts_co')
        },                        
        {
          value: 4,
          text: this.$t('account.fts_bethelite')
        },        
        {
          value: 5,
          text: this.$t('account.fts_construction')
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


    getShiftStatus(shift, user) {
      var result = {
        status: null,
        text: '',
        color: '',
        icon: ''
      }

      var shiftUser = shift.users.filter(u => u.id == user.id)
      if (shiftUser.length > 0) {
        result = {
          status: shiftUser[0].pivot.status,
          text: this.shiftStatus[shiftUser[0].pivot.status].text,
          color: this.shiftStatus[shiftUser[0].pivot.status].color,
          icon: this.shiftStatus[shiftUser[0].pivot.status].icon
        }
      }

      return result
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

    async refreshUser () {
      await this.$store.dispatch('auth/fetchUser')
    },

    async refreshTeam() {
      await this.$store.dispatch('auth/refreshTeam')
    },

    async getTeams () {
      await this.$store.dispatch('auth/fetchUser')
    },

    async getRoles () {
      await this.$store.dispatch('auth/fetchUser')
    },

    async getMessageCounts () {
      await this.$store.dispatch('general/scheduledTasks')
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
    async storeSchedule (data) {
      this.$store.commit('scheduling/SET_SCHEDULE', data )
    },

    async storeShifts (data) {
      this.$store.commit('scheduling/SET_SHIFTS', data)
    },

    async storeUserShifts (data) {
      this.$store.commit('scheduling/SET_USER_SHIFTS', data)
    },

    async storeShiftUsers (data) {
      this.$store.commit('scheduling/SET_SHIFT_USERS', data)
    },

    async storeShiftsAvailable (data) {
      this.$store.commit('scheduling/SET_SHIFTS_AVAILABLE', data)
    },

    async storeShiftConflicts (data) {
      this.$store.commit('scheduling/SET_SHIFT_CONFLICTS', data)
    },

    async storeTrades (data) {
      this.$store.commit('scheduling/SET_TRADES', data)
    },

    async storeTeamUsers (data) {
      this.$store.commit('scheduling/SET_TEAM_USERS', data)
    },


    filterShiftsAvailability (shifts, user) {
      var avail_shifts = shifts.filter(shift => 
        shift.users.filter(u => u.id == user.id).length > 0 || // include shift if user is already assigned to it, regardless of availability
        this.checkShiftAvailability(shift, user)
      )
      return avail_shifts
    },

    filterUsersAvailability (shift, users, users_with_availability) {

      var avail_users = users.filter(user => 
        shift.users.filter(u => u.id == user.id).length > 0 || // include user if he is already assigned, regardless of availability
        this.checkShiftAvailability(shift, users_with_availability.filter(a => a.id == user.id))
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
      if (user.length != undefined && user.length > 0) {
        user = user[0]
      }
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
        var check = availability.filter(a => 
          a.day_of_week == shift_dow && 
          a.available == 1 &&
          (
            (shift_start_hour >= this.$dayjs('2001-01-01 ' + a.start_time).hour() &&
            shift_start_hour < this.$dayjs('2001-01-01 ' + a.end_time).hour()) ||
            (shift_end_hour > this.$dayjs('2001-01-01 ' + a.start_time).hour() &&
            shift_end_hour <= this.$dayjs('2001-01-01 ' + a.end_time).hour())
          )
        );

        result = check.length > 0
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


    checkShiftConflicts (shift, user_shifts, alwaysCheck = false, useStore = true) {
      // Day of Week: Monday = 1, Sunday = 7 (ISO standard)

      if (useStore) {
        var store_conflicts = this.$store.getters['scheduling/shift_conflicts'] || []
        store_conflicts = store_conflicts.filter(c => c.shift != shift.id)
      }

      var shift_conflicts = { shift: shift.id, conflicts: [] }


      const shift_date = this.$dayjs(shift.time_start).format('YYYY-MM-DD')
      const shift_start_hour = this.$dayjs(shift.time_start).hour()
      var shift_end_hour = this.$dayjs(shift.time_end).hour()

      if (this.$dayjs(shift.time_end).minute() > 0) {
        shift_end_hour += 1
      }
    

      // Only check for conflicts if the user is assigned to this shift
      var in_shift = user_shifts.filter(s => s.id == shift.id && s.pivot.status != 3)

      if (alwaysCheck || in_shift.length > 0) { 

        // First, check shift against user's other assigned shifts in all teams
        if (user_shifts && user_shifts.length > 0) {
          var check = user_shifts.filter(s => 
            shift.id != s.id &&
            //(shift.pivot.status != undefined ? shift.pivot.status != 3 : true) &&
            s.pivot.status != 3 &&
            (
              this.$dayjs(shift.time_start).isBetween(this.$dayjs(s.time_start), this.$dayjs(s.time_end), 'minute', '[)') ||
              this.$dayjs(shift.time_end).isBetween(this.$dayjs(s.time_start), this.$dayjs(s.time_end), 'minute', '(]')
            )
          );

          check.forEach(s => shift_conflicts.conflicts.push({ type: 'conflict', team: s.schedule.team.display_name }))
        }


        // Next, check and warn for any adjacent shifts at a different location
        if (user_shifts && user_shifts.length > 0) {
          var check = user_shifts.filter(s => 
            shift.id != s.id &&
            //(shift.pivot.status != undefined ? shift.pivot.status != 3 : true) &&
            s.pivot.status != 3 &&
            shift.location_id != s.location_id &&
            (this.$dayjs(shift.time_start).isSame(this.$dayjs(s.time_end)) ||
            this.$dayjs(shift.time_end).isSame(this.$dayjs(s.time_start)))
          );

          check.forEach(s => shift_conflicts.conflicts.push({ type: 'adjacent', team: s.schedule.team.display_name }))
        }


        // Remove duplicate warnings
        shift_conflicts.conflicts = shift_conflicts.conflicts.filter((v,i,a)=>a.findIndex(t=>(t.type === v.type && t.team===v.team))===i)
        
        if (useStore) {
          store_conflicts.push(shift_conflicts)
          this.storeShiftConflicts(store_conflicts)
        }

      }

      return shift_conflicts.conflicts

    },


    checkConflictsAllUserShifts() {
      var user_shifts = this.$store.getters['scheduling/user_shifts'] || []
      this.storeShiftConflicts([])

      if (user_shifts.length > 0) {
        user_shifts.forEach(shift => {
          this.checkShiftConflicts(shift, user_shifts)
        })
      }
    },


    checkConflictsAllUserShifts() {
      var user_shifts = this.$store.getters['scheduling/user_shifts'] || []
      this.storeShiftConflicts([])

      if (user_shifts.length > 0) {
        user_shifts.forEach(shift => {
          this.checkShiftConflicts(shift, user_shifts)
        })
      }
    }
  }
}



export const messages = {
  methods: {
    message_trade_offer (publisher, time_start, time_end, location, language = null) {
      const lang = language || this.team.language || 'en'     

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
    },

    
    async send_message_user_inbox (message) {
      /**
       * Requires:
       *  - recipient_id
       *  - message_text
       *  - named_route (nullable)
       *  - color (nullable)
       *  - type (nullable)
       *  - icon (nullable)
       *  - show_banner
       *  - expires_on (nullable)
       */

      await axios({
        method: 'POST',      
        url: '/api/messages',
        data: {
          recipient_id: message.recipient.id,
          recipient_type: 'App\\Models\\User',
          for_roles: null,
          system_message: null,
          message_text: message.message_text,
          message_i18n_string: null,
          named_route: message.named_route,
          color: message.color || '#7E7E7E',
          type: message.type || 'info',
          icon: message.icon,
          dismissable: true,
          outlined: true,
          show_banner: message.show_banner,
          expires_on: message.expires_on 
        }
      })
    }

  }
}


export default helper
