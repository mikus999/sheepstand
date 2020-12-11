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
      @click:date="filterShifts()"
      width="100%"
    >
    </v-date-picker>


    <v-switch 
      v-model="showAll" 
      :label="$t('shifts.show_all_dates')" 
      hide-details
      @change="toggleShowAllNone"
    />

    <v-switch 
      v-model="filter_shifts" 
      :label="$t('shifts.show_according_to_availability')" 
      hide-details
      @change="filterShifts"
    />

    <v-row class="my-6">
      <span class="mx-auto text-overline">{{ this.$t('schedules.shifts_displayed', [shiftsFiltered.length, shifts.length]) }}</span>
    </v-row>

    <v-divider class="mb-8" />

    <v-row 
      class="ma-2"
      v-for="d in getUniqueDates()"
      :key="d"
    >

      <div class="text-h5 mx-auto mb-4">
        {{ $dayjs(d).format('ddd, L') }}
      </div>

      <v-col cols=12>
        <ShiftCard 
          v-for="shift in sortedShifts(d)" 
          :key="shift.id" 
          :shift="shift" 
          :schedule="shift.schedule" 
          :user_shifts="user_shifts"
          class="my-5"
        ></ShiftCard>
      </v-col>

      <v-col>
        <v-divider class="my-8" />
      </v-col>
      
    </v-row>
  </v-card>
</template>

<script>
import axios from 'axios'
import { helper, scheduling } from '~/mixins/helper'
import ShiftCard from '~/components/ShiftCard.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, scheduling],
  components: {
    ShiftCard
  },

  data () {
    return {
      tab: null,
      shifts: [],
      schedules: null,
      schedule: null,
      user_shifts: null,
      hover: false,
      availableDates: [],
      selectedDates: [],
      shiftsFiltered: [],
      showAll: true,
      filter_shifts: true,
    }
  },

  computed: {

    calendarHeader() {
      var headerString = ''
      headerString += this.$t('schedules.shifts_displayed') + ': ' + this.shiftsFiltered.length
      return headerString
    },

  },

  created() {
    this.initialize()
  },

  methods: {
    async initialize() {
      this.getUserShifts()
    },

    async getUserShifts () {
      await axios.get('/api/user/shifts')
        .then(response => {
          this.user_shifts = response.data
          this.getSchedData()
        })
    },

    allowedDates (val) {
      return this.availableDates.indexOf(val) > -1
    },


    getUniqueDates() {
      var shiftTemp = []

      // Get the dates for selected shifts
      this.shiftsFiltered.forEach((shift) => {
        shiftTemp.push(this.$dayjs(shift.time_start).format('YYYY-MM-DD'))
      })

      // Get unique values only
      shiftTemp = Array.from(new Set(shiftTemp))

      // Sort the remaining dates
      shiftTemp.sort( ( a, b) => {
        return new Date(a) - new Date(b);
      });

      return shiftTemp
    },
    
    sortedShifts (date) {
      var shiftTemp = this.shiftsFiltered.filter(shift => this.$dayjs(shift.time_start).format('YYYY-MM-DD') == date)

      shiftTemp.sort( ( a, b) => {
        return new Date(a.time_start) - new Date(b.time_start);
      });
      
      return shiftTemp;
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

          // If there are more than three locations for the day, just display one black dot
          if (color_array.length > 3) {
            color_array = []
          }

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

      // First, filter by date (or show all dates)
      this.shiftsFiltered = this.shifts.filter(s => this.selectedDates.indexOf(this.$dayjs(s.time_start).format('YYYY-MM-DD')) > -1)

      this.showAll = (this.shiftsFiltered.length == this.shifts.length)


      // Second, filter by user availability (if option is checked)
      if (this.filter_shifts) {
        this.shiftsFiltered = this.filterShiftsAvailability(this.shiftsFiltered, this.user)
      }

    },

    toggleShowAllNone() {
      if (this.showAll) {
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