<template>
  <v-card width="100%">
    <v-toolbar flat extended height="80">
      <v-select outlined v-model="schedule" :items="schedules" item-value="id" item-text="date_start" return-object
        @change="getShiftData" prepend-icon="mdi-calendar-week-begin" :label="$t('schedules.week_of')" class="mt-10">

        <template v-slot:selection="{ item }">
          <div v-if="item">
            <v-icon :color="item.status == 1 ? 'green' : 'red'">mdi-circle</v-icon>
            {{ item.date_start }}<span v-if="$vuetify.breakpoint.smAndUp">: {{scheduleStatus[item.status].text_user }}</span>
          </div>
        </template>

        <template v-slot:item="{ item }">
          <div v-if="item">
            <v-icon :color="item.status == 1 ? 'green' : 'red'">mdi-circle</v-icon>
            {{ item.date_start }}<span v-if="$vuetify.breakpoint.smAndUp">: {{scheduleStatus[item.status].text_user }}</span>
          </div>
        </template>
      </v-select>

      <template v-slot:extension>
        <v-tabs v-if="schedule !== null" v-model="tab" centered align-with-title fixed-tabs show-arrows>
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
        <v-row class="ma-2">
          <ShiftCard v-for="shift in filterShifts(shifts, n)" :key="shift.id" :shift="shift" :schedule="schedule" :width="cardWidth" class="ma-3"></ShiftCard>
        </v-row>
      </v-tab-item>
    </v-tabs-items>
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

    async getSchedData () {
      await axios.get('/api/schedules/' + this.team.id)
        .then(response => {
          this.schedules = response.data

          // Filter schedules to only show those with status of 1 (ASSIGNMENTS) or 2 (FINAL)
          this.schedules = this.schedules.filter(sched => ["1","2"].indexOf(sched.status) > -1)
                  
          const prevWeek = this.$dayjs().subtract(8, 'd').format('YYYY-MM-DD')
          this.schedules = this.schedules.filter(sched => (sched.date_start >= prevWeek))

          if (this.schedules[0] !== undefined) {
            this.schedule = this.schedules[0]
            this.getShiftData()
          }
        })
    },

    async getShiftData () {
      await axios.get('/api/schedules/' + this.schedule.id + '/shifts')
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