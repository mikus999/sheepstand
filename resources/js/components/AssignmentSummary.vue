<template>
  <v-card>
    <v-card-title class="justify-center">
      <v-icon left>{{ icons.mdiCalendarWeek }}</v-icon>
      {{ $t('schedules.week_of') }} {{ schedule.date_start | formatDate }}
    </v-card-title>

    <v-card-text class="mt-6">
      <v-row v-for="i in teamData" :key="i.name">
        <v-col>
          {{ i.name }}
        </v-col>
        <v-col>
          {{ i.shifts }}
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
        var user_shifts = u.shifts.filter(f => f.schedule_id == this.schedule.id)        
        
        var data = {
          name: user_name,
          shifts: user_shifts.length
        }

        this.teamData.push(data)
      });


      // Sort the array alphabetically
      this.teamData = this.teamData.sort((a,b) => {
        return b.shifts - a.shifts
      })
    }

  }
}
</script>