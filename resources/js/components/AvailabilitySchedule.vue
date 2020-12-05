<template>
  <v-card width="100%">
    <v-card-title class="text-h6">
      <v-icon class="mr-3">mdi-calendar-multiselect</v-icon>
      {{ $t('account.availability') }}
    </v-card-title>

    <v-card-text>
      <v-row width="100%">
        <v-col cols="2">
          <br>
          <div v-for="h in 24" :key="'row_' + h" class="avail_row">
            {{ timeSlot(h) }}
          </div>
        </v-col>

        <v-col cols="1" v-for="d in 7" :key="'day_' + (d-1)" class="text-center">
          {{ dayOfWeek(d) }}
          <div v-for="h in filterAvailability(d-1)" :key="'hour_' + (h.start_time)" class="avail_square">
            {{ h.day_of_week}}
          </div>
        </v-col>

        <v-col cols="3"></v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: "AvailabilitySchedule",
  mixins: [helper],

  props: {
    data: {
      type: [Object, Array]
    }
  },

  data() {
    return {
      availability: []
    }
  },


  created() {
    this.getAvailability()
  },

  methods: {
    async getAvailability () {
      await axios.get('/api/account/availability')
        .then(response => {
          this.availability = response.data
        })
    },

    filterAvailability(day) {
      var dayAvail = this.availability.filter(d => d.day_of_week == day)
      return dayAvail
    },

    dayOfWeek (daynum) {
      var days = []
      days = this.$dayjs().localeData().weekdaysShort()
      days[7] = days[0]
      return days[daynum]
    },

    timeSlot(hour) {
      const startHour = this.$dayjs().hour(hour-1).minute(0).format('LT')
      const endHour = this.$dayjs().hour(hour).minute(0).format('LT')

      return startHour + ' - ' + endHour
    }
  },
}
</script>

<style scoped>
  .avail_row {
    height: 35px;
    margin: 2px;
    display: inline-block;
  }

  .avail_square {
    width: 35px;
    height: 35px;
    margin: 2px;
    display: inline-block;
    border-radius: 1.5px;
    background-color: #cccccc;
  }
</style>