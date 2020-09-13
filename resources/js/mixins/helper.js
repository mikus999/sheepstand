import { mapGetters, mapState } from 'vuex'

const helper = {
  computed: {
    ...mapGetters({
      user: 'auth/user',
      team: 'teams/getTeam',
      teams: 'teams/getTeams',
      hasTeam: 'teams/hasTeam',
      locale: 'lang/locale',
      locales: 'lang/locales'
    }),
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
          color: 'deep-orange'
        },
        { 
          value: 1,
          text: this.$t('shifts.status_1'), 
          color: 'deep-orange'
        },
        { 
          value: 2,
          text: this.$t('shifts.status_2'), 
          color: 'green'
        },
        { 
          value: 3,
          text: this.$t('shifts.status_3'), 
          color: 'red darken-4'
        },
        { 
          value: 4,
          text: this.$t('shifts.status_4'), 
          color: 'blue'
        }
      ]
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

    setTeam (teamid, route) {
      this.$store.dispatch('teams/setTeam', { teamid })
      this.getTeams()

      if (route !== undefined) {
        if (this.$route.name !== route) { 
          this.$router.push({ name: route })
        }
      }
    },

    async getTeams () {
      await this.$store.dispatch('teams/fetchTeams')
    },

    setLocale (locale) {
      if (this.$i18n.locale !== locale) {
        this.$i18n.locale = locale
        this.$store.dispatch('lang/setLocale', { locale })
      }
    },

    async logout () {
      // Log out the user.
      await this.$store.dispatch('auth/logout')

      // Redirect to login.
      this.$router.push({ name: 'login' })
    },
  },

}

export default helper
