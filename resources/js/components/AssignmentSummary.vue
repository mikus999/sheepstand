<template>
  <v-card>
    <v-card-title class="justify-center" :width="width">
      <v-icon left>{{ icons.mdiCalendarWeek }}</v-icon>
      {{ $t('schedules.week_of') }} {{ schedule.date_start | formatDate }}
    </v-card-title>

    <v-card-text class="mt-6 overflow-auto" style="height: 600px;">
      <v-row class="font-weight-bold">
        <v-col>{{ $t('general.name') }}</v-col>
        <v-col class="text-center">{{ $t('schedules.shifts') }}</v-col>
        <v-col class="text-center">{{ $t('shifts.shifts_30') }}</v-col>
      </v-row>

      <v-row v-for="i in teamData" :key="i.name">
        <v-col>
          {{ i.name }}
        </v-col>
        <v-col class="text-center">
          {{ i.shifts_curr }}
        </v-col>
        <v-col class="text-center">
          {{ i.shifts_30 }}
        </v-col>
      </v-row>
    </v-card-text>


    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="primary" text v-on:click="$emit('close')">
        {{ $t('general.close' ) }}
      </v-btn>
    </v-card-actions>

  </v-card>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper } from '~/mixins/helper'

export default {
  name: 'AssignmentSummary',
  mixins: [helper],
  props: {
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '500px'
    },
  },

  data() {
    return {
      teamData: []
    }
  },

  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      team_users: 'scheduling/team_users',
    }),
  },

  created() {
    this.initialize()
  },

  methods: {
    async initialize() {
      this.getSummary()
    },

    getSummary() {

      this.team_users.forEach(u => {
        var user_name = u.name
        var user_shifts1 = u.shifts.filter(f => f.schedule_id == this.schedule.id)        
        var user_shifts30 = u.shifts.filter(f => 
          (this.$dayjs(f.time_start) >= this.$dayjs(this.schedule.date_start).subtract(1, 'month')) &&
          (this.$dayjs(f.time_start) <= this.$dayjs(this.schedule.date_start).add(1, 'week'))
        )

        var data = {
          name: user_name,
          shifts_curr: user_shifts1.length,
          shifts_30: user_shifts30.length
        }

        this.teamData.push(data)
      });


      // Sort the array alphabetically
      this.teamData = this.teamData.sort((a,b) => {
        return b.shifts_curr - a.shifts_curr
      })
    }

  }
}
</script>