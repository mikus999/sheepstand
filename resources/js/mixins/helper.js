const helper = {
  data () {
    return {
      scheduleStatus: [
        { 
          value: 0,
          text: this.$t('schedules.status_draft'), 
          color: 'yellow'
        },
        { 
          value: 1,
          text: this.$t('schedules.status_published'), 
          color: 'blue'
        },
        { 
          value: 2,
          text: this.$t('schedules.status_final'), 
          color: 'green'
        },
        { 
          value: 3,
          text: this.$t('schedules.status_archived'), 
          color: 'red'
        },
        { 
          value: 4,
          text: this.$t('schedules.status_unknown'), 
          color: 'grey'
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
  },

}

export default helper
