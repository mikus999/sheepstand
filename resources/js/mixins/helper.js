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
          icon: 'mdi-account-switch'
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
      this.$router.go()
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
    }
  },

}

export default helper
