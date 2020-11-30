<template>
  <v-card width="100%">
    <v-date-picker
      v-model="selectedDates"
      :allowed-dates="allowedDates"
      :events="dayShifts"
      :locale="locale"
      :first-day-of-week="$dayjs().localeData().firstDayOfWeek()"
      no-title
      multiple
      @click:date="filterShifts"
      width="100%"
    >
    </v-date-picker>

    <v-btn
      block
      color="primary"
      @click="toggleShowAllNone"
      >
      <v-icon small class="mr-2">{{ showAllNone ? 'mdi-eye-off' : 'mdi-eye' }}</v-icon>
      {{ showAllNone ? $t('schedules.show_none') : $t('schedules.show_all') }}
    </v-btn>


    <v-row class="mb-2 mt-6">
      <span class="mx-auto text-overline">{{ calendarHeader }}</span>
    </v-row>

    <v-row class="ma-2">
      <ShiftCard v-for="shift in sortedShifts" :key="shift.id" :shift="shift" :schedule="shift.schedule" :width="cardWidth" class="ma-3"></ShiftCard>
    </v-row>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftCard from '~/components/ShiftCard.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    ShiftCard
  },

  data () {
    return {
      tab: null,
      shifts: [],
      schedules: null,
      schedule: null,
      hover: false,
      availableDates: [],
      selectedDates: [],
      shiftsFiltered: [],
      showAllNone: true,
    }
  },

  computed: {

    calendarHeader() {
      var headerString = ''
      headerString += this.$t('schedules.shifts_displayed') + ': ' + this.shiftsFiltered.length
      return headerString
    },

    sortedShifts: function() {
      this.shiftsFiltered.sort( ( a, b) => {
          return new Date(a.time_start) - new Date(b.time_start);
      });
      return this.shiftsFiltered;
    },

    cardWidth () {
      var width = null

      switch (this.$vuetify.breakpoint.name) {
        case 'xs':
          width = '100%'
          break
        case 'sm':
          width = '29%'
          break
        case 'md':
          width = '22%'
          break
        case 'xl':
          width = '16%'
          break
        default:
          width = '22%'
      }

      return width
    }
  },

  created() {
    this.getSchedData()
  },

  methods: {
    allowedDates (val) {
      return this.availableDates.indexOf(val) > -1
    },

    dayShifts (date) {
      if (this.shifts) {
        var shiftTemp = this.shifts.filter(shift => this.$dayjs(shift.time_start).format('YYYY-MM-DD') == date)

        if (shiftTemp.length > 0) {
          var color_array = []
          // Loop through all shifts for the day and add the location color to the array
          shiftTemp.forEach(shift => {
            // If the array does not already contain this color, add it to the array
            if (color_array.indexOf(shift.location.color_code) < 0) {
              color_array.push(shift.location.color_code)
            }
          })
          return color_array
        }

        return false
      }
    },


    async getSchedData () {
      await axios.get('/api/schedules/' + this.team.id)
        .then(response => {
          this.schedules = response.data

          // Filter schedules to only show those with status of 1 (ASSIGNMENTS) or 2 (FINAL)
          this.schedules = this.schedules.filter(sched => ["1","2"].indexOf(sched.status) > -1)
                  
          // Only show current and future schedules
          const prevWeek = this.$dayjs().subtract(8, 'd').format('YYYY-MM-DD')
          this.schedules = this.schedules.filter(sched => (sched.date_start >= prevWeek))


          this.schedules.forEach(schedule => {
            this.getShiftData(schedule)
          })

        })

    },

    async getShiftData (schedule) {
      await axios.get('/api/schedules/' + schedule.id + '/shifts')
        .then(response => {
          Array.prototype.push.apply(this.shifts, response.data)

          response.data.forEach((shift) => {
            this.availableDates.push(this.$dayjs(shift.time_start).format('YYYY-MM-DD'))
            this.selectedDates.push(this.$dayjs(shift.time_start).format('YYYY-MM-DD'))
            this.filterShifts()
          })
        })

    },

    dayOfWeek (daynum) {
      var days = []
      days = this.$dayjs().localeData().weekdaysShort()
      days[7] = days[0]
      return days[daynum]
    },

    filterShifts () {
      if (this.selectedDates.length > 0) {
        this.shiftsFiltered = this.shifts.filter(s => this.selectedDates.indexOf(this.$dayjs(s.time_start).format('YYYY-MM-DD')) > -1)
      } else {
        this.shiftsFiltered = []
      }
    },

    toggleShowAllNone() {
      this.showAllNone = !this.showAllNone

      if (this.showAllNone) {
        this.shifts.forEach((shift) => {
          this.availableDates.push(this.$dayjs(shift.time_start).format('YYYY-MM-DD'))
          this.selectedDates.push(this.$dayjs(shift.time_start).format('YYYY-MM-DD'))
          this.filterShifts()
        })
      } else {
        this.selectedDates = []
        this.shiftsFiltered = []
      }
    },
  }

}
</script>