const helper = {
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
