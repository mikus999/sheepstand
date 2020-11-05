import { mapGetters, mapState } from 'vuex'
import axios from 'axios'


const helper = {
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
      locales: 'lang/locales'
    })
  },

  data () {
    return {
      scheduleStatus: [
        { 
          value: 0,
          text: this.$t('schedules.status_0'), 
          color: 'yellow'
        },
        { 
          value: 1,
          text: this.$t('schedules.status_1'), 
          color: 'blue'
        },
        { 
          value: 2,
          text: this.$t('schedules.status_2'), 
          color: 'green'
        },
        { 
          value: 3,
          text: this.$t('schedules.status_3'), 
          color: 'red'
        },
        { 
          value: 4,
          text: this.$t('schedules.status_4'), 
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
      ]
    }
  },

  created () {
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

export default helper
