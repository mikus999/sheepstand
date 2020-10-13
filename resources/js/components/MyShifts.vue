<template>
  <v-card>
    <v-data-table :headers="headers" :items="shifts" disable-sort 
      show-expand single-expand :expanded.sync="expanded" width="100%">
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>mdi-calendar-account</v-icon>
            {{ $t('shifts.my_shifts') }}
          </v-toolbar-title>
        </v-toolbar>

        <v-switch v-model="allTeams" :label="$t('shifts.show_all_teams')"  hide-details class="mx-4" @change="update" />

      </template>


      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}<br>
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
      </template>

      <template v-slot:item.location="{ item }">
        <v-chip label small :color="item.location.color_code">{{ item.location.name }}</v-chip>
      </template>

      <template v-slot:expanded-item="{ headers, item }">
        <td :colspan="headers.length">
          <v-row>
            <v-col cols=12 sm=6 style="vertical-align: top">
              <div class="text-overline">Participants</div>
              <div v-for="user in item.users" :key="user.id" class="ma-2" :title="shiftStatus[user.pivot.status].text" disabled>
                <v-icon class="ml-n4 mr-2" :color="shiftStatus[user.pivot.status].color">{{ shiftStatus[user.pivot.status].icon }}</v-icon>
                <span :class="shiftStatus[user.pivot.status].color + '--text'">{{ user.name }}</span>
              </div>
            </v-col>
            <v-col cols=12 sm=6 style="vertical-align: top">
              <div class="text-overline">Location Details</div>
            </v-col>
          </v-row>
        </td>
      </template>
    </v-data-table>
  </v-card>
</template>


<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'MyShifts',
  mixins: [helper],
  props: {},
  components: {
  },

  data () {
    return {
      expanded: [],
      shiftOverlay: false,
      shift: null,
      schedule: null,
      shifts: [],
      shiftsAll: [],
      allTeams: false,
      headers: [
        { text: this.$t('teams.team_name'), value: 'schedule.team.display_name', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: '', value: 'data-table-expand' },
      ],
    }
  },


  created () {
    this.getShifts()
  },

  methods: {
    async getShifts () {
      await axios.get('/api/user/shifts')
        .then(response => {
          this.shiftsAll = response.data

          this.shifts = response.data.filter(shift => shift.schedule.team_id === this.team.id)

        })
    },

    update() {
      if (this.allTeams) {
          this.shifts = this.shiftsAll
      } else {
          this.shifts = this.shiftsAll.filter(shift => shift.schedule.team_id === this.team.id)
      }
    }
  }
}

</script>