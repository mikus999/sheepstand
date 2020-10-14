<template>
  <v-card>
    <v-data-table :headers="headers" :items="shifts" disable-sort 
       width="100%">
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

      <template v-slot:item.view="{ item }">
        <v-icon @click="showShiftOverlay(item)">mdi-card-account-details-outline</v-icon>
      </template>

    </v-data-table>

    <v-overlay :value="shiftOverlay" @click.native="shiftOverlay = false">
      <ShiftCard :shift="shift" :schedule="schedule" onlyinfo width="300px" height="100%"></ShiftCard>
    </v-overlay>
  </v-card>
</template>


<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftCard from '~/components/ShiftCard.vue'

export default {
  name: 'MyShifts',
  mixins: [helper],
  props: {},
  components: {
    ShiftCard
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
        { text: '', value: 'view' },
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
    },

    showShiftOverlay(shift) {
      this.shift = shift
      this.schedule = shift.schedule
      this.shiftOverlay = true
    }
  }
}

</script>