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
          return 'DRAFT'
        case '1':
          return 'PUBLISHED'
        case '2':
          return 'FINAL'
        case '3':
          return 'DELETED'
        default:
          return 'UNKNOWN'
      }
    },

    getScheduleStatusColor (status) {
      switch (status) {
        case '0':
          return 'red'
        case '1':
          return 'yellow'
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
