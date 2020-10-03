<template>
  <v-container fluid>
    <v-toolbar flat extended height="80">
      <v-select outlined v-model="schedule" :items="schedules" item-value="id" item-text="date_start" 
        @change="getShiftData" prepend-icon="mdi-calendar-week-begin" :label="$t('schedules.week_of')" class="mt-10">
      </v-select>

      <template v-slot:extension>
        <v-tabs v-model="tab" centered align-with-title fixed-tabs show-arrows v-if="schedule !== null">
          <v-tab v-for="n in 7" :key="n">
            <v-badge color="secondary" :value="filterShifts(shifts, n) ? filterShifts(shifts, n).length : null" 
              :content="filterShifts(shifts, n) ? filterShifts(shifts, n).length : null" offset-x="-3px">
              {{ dayOfWeek(n) }}
            </v-badge>
          </v-tab>
        </v-tabs>
      </template>
    </v-toolbar>

    <v-tabs-items v-model="tab">
      <v-tab-item v-for="n in 7" :key="n">
        <v-row>
          <ShiftCard v-for="shift in filterShifts(shifts, n)" :key="shift.id" :shift="shift" :width="cardWidth" class="ma-3"></ShiftCard>
        </v-row>
      </v-tab-item>
    </v-tabs-items>
  </v-container>
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
      shifts: null,
      schedules: null,
      schedule: null,
      hover: false,
    }
  },

  computed: {

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

    async getSchedData () {
      await axios.get('/api/schedules/' + this.team.id)
        .then(response => {
          this.schedules = response.data
          this.schedules = this.schedules.filter(sched => ["1","2"].indexOf(sched.status) > -1)

          if (this.schedules[0] !== undefined) {
            this.schedule = this.schedules[0].id
            this.getShiftData()
          }
        })
    },

    async getShiftData () {
      await axios.get('/api/schedules/' + this.schedule + '/shifts')
        .then(response => {
          this.tab = 0
          this.shifts = response.data
        })

    },

    dayOfWeek (daynum) {
      var days = []
      days = this.$dayjs().localeData().weekdaysShort()
      days[7] = days[0]
      return days[daynum]
    },

    filterShifts (shifts, day) {
      var temp = null

      if (shifts !== null) {
        temp = shifts.filter(s => this.$dayjs(s.time_start).day() == day)
      }
      return temp
    }
  }

}
</script>