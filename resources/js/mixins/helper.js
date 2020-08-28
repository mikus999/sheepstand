const helper = {
  methods: {
    formatJSON (data) {
      if (data.name) {
        return JSON.parse(JSON.stringify(data))
      } else {
        return JSON.parse(data)
      }
    },

    getScheduleStatusText (status) {
      switch (status) {
        case '0':
          return this.$t('schedules.status_draft')
        case '1':
          return this.$t('schedules.status_published')
        case '2':
          return this.$t('schedules.status_final')
        case '3':
          return this.$t('schedules.status_archived')
        default:
          return this.$t('schedules.status_unknown')
      }
    },

    getScheduleStatusColor (status) {
      switch (status) {
        case '0':
          return 'red'
        case '1':
          return 'blue'
        case '2':
          return 'green'
        case '3':
          return 'grey'
        default:
          return ''
      }
    }
  },

}

export default helper
