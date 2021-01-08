<template>
  <v-card width="100%" flat>
    <v-toolbar flat>
      <v-toolbar-title>
        <v-icon left>{{ icons.mdiCalendarMultiselect }}</v-icon>
        {{ $t('schedules.available_shifts') }}
      </v-toolbar-title>
    </v-toolbar>


    <v-expansion-panels accordion flat focusable>
      <v-expansion-panel>
        <v-expansion-panel-header>
          <div>
            <v-icon left>{{ icons.mdiTune }}</v-icon>
            {{ $t('general.filters') }}
          </div>
        </v-expansion-panel-header>

        <v-expansion-panel-content>
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


          <div>
            <v-switch 
              v-model="showAll" 
              hide-details
              @change="toggleShowAllNone"
              class="my-0"
            >
              <template v-slot:label>
                <span class="switch-label">{{ $t('shifts.show_all_dates') }}</span>
              </template>
            </v-switch>

            <v-switch 
              v-model="filter_shifts" 
              hide-details
              @change="filterShifts"
              class="my-0"
            >
              <template v-slot:label>
                <span class="switch-label">{{ $t('shifts.show_according_to_availability') }}</span>
              </template>
            </v-switch>

            <v-switch 
              v-model="filter_closed_schedules" 
              hide-details
              @change="filterShifts"
              class="my-0"
            >
              <template v-slot:label>
                <span class="switch-label">{{ $t('shifts.show_closed_schedules') }}</span>
              </template>
            </v-switch>

            <v-switch 
              v-model="filter_open_availability" 
              hide-details
              @change="filterShifts"
              class="my-0"
            >
              <template v-slot:label>
                <span class="switch-label">{{ $t('shifts.show_open_availability') }}</span>
              </template>
            </v-switch>

          </div>



          <v-row class="my-6">
            <span class="mx-auto text-overline">{{ this.$t('schedules.shifts_displayed', [shiftsFiltered.length, shifts.length]) }}</span>
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>


    <v-divider class="mb-8" />

    <v-row 
      v-for="d in getUniqueDates()"
      :key="d"
       class="my-16"
    >

      <div class="text-h5 mx-auto mb-2">
        {{ $dayjs(d).format('ddd, L') }}
      </div>

      <v-col cols=12>
        <v-expansion-panels>
          <ShiftCardMobile 
            v-for="item in sortedShifts(d)"
            :key="item.id" 
            :shift="item"
            v-on:location="showLocationOverlay(item)"
            />
        </v-expansion-panels>

      </v-col>

    </v-row>



    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
      <Leaflet :location="location" :width="mapWidth" height="500px" readonly 
          v-on:close="locationOverlay = false" v-on:click.native.stop />
    </v-overlay>
  </v-card>
</template>

<script>
import axios from 'axios'
import { helper, scheduling } from '~/mixins/helper'
import ShiftCardMobile from '~/components/ShiftCardMobile.vue'
import Leaflet from '~/components/Leaflet.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, scheduling],
  components: {
    ShiftCardMobile,
    Leaflet
  },

  data () {
    return {
      tab: null,
      location: null,
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
      filter_closed_schedules: true,
      filter_open_availability: true,
      locationOverlay: false,
      calendarPanel: false,
    }
  },

  computed: {
    calendarHeader() {
      var headerString = ''
      headerString += this.$t('schedules.shifts_displayed') + ': ' + this.shiftsFiltered.length
      return headerString
    },
    
    mapWidth() {
      return this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
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
          this.user_shifts = response.data.data.shifts
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
      if (this.shiftsFiltered) {
        var shiftTemp = this.shiftsFiltered.filter(shift => this.$dayjs(shift.time_start).format('YYYY-MM-DD') == date)

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
          this.schedules = response.data.data.schedules

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
          Array.prototype.push.apply(this.shifts, response.data.data.shifts)

          response.data.data.shifts.forEach((shift) => {
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

      // Third, filter by closed/open schedule (depending on switch)
      if (this.filter_closed_schedules) {
        this.shiftsFiltered = this.shiftsFiltered.filter(s => 
            s.schedule.status == 1 ||
            this.isShiftMember(s)
          )
      }

      // Fourth, filter by open availability (depending on switch)\
      if (this.filter_open_availability) {
        this.shiftsFiltered = this.shiftsFiltered.filter(s => 
            this.getNumberOpenSpots(s) > 0 ||
            this.isShiftMember(s)
          )
      }

    },

    async toggleShowAllNone() {
      if (this.showAll) {
        this.shifts.forEach((shift) => {
          this.availableDates.push(this.$dayjs(shift.time_start).format('YYYY-MM-DD'))
          this.selectedDates.push(this.$dayjs(shift.time_start).format('YYYY-MM-DD'))
        })
        this.filterShifts()

      } else {
        this.selectedDates = []
        this.shiftsFiltered = []
      }
    },


    filterShiftUsers(shiftUsers) {
      return shiftUsers.filter(u => u.pivot.status != 3)
    },

    

    showLocationOverlay(shift) {
      if (shift.location.map != null) {
        this.location = shift.location
        this.locationOverlay = true
      }
    },


    isShiftMember(shift) {
      var temp = shift.users.map(o => o['id'])
      var index = temp.indexOf(this.user.id)
      return index > -1
    },

    getNumberOpenSpots(shift) {
      return this.returnZero(shift.max_participants - this.filterShiftUsers(shift.users).length)
    },

    returnZero (n) {
      return n < 0 ? 0 : n
    }

  }

}
</script>


<style scoped>
  .switch-label
  {
    font-size: .85rem !important;
  }

</style>