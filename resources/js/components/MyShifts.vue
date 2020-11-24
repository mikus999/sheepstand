<template>
  <v-card>
    <v-data-table :headers="headers" :items="shifts" disable-sort width="100%">
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
        <v-chip label small :color="item.location.color_code" @click="showLocationOverlay(item)">{{ item.location.name }}</v-chip>
      </template>

      <template v-slot:item.view="{ item }">
        <v-btn icon small @click="showShiftOverlay(item)">
          <v-icon small>mdi-card-account-details-outline</v-icon>
        </v-btn>

        <v-btn icon small :disabled="!showTradeButton(item)" :color="item.pivot.status == 4 ? 'blue' : ''" @click.stop="updateTrade(item)">
          <v-icon small>mdi-account-convert</v-icon>
        </v-btn>
      </template>

    </v-data-table>

    <v-overlay :value="shiftOverlay" @click.native="shiftOverlay = false">
      <ShiftCard :shift="shift" :schedule="schedule" onlyinfo width="300px" height="100%"></ShiftCard>
    </v-overlay>

    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false">
      <Leaflet :location="location" :width="mapWidth" height="500px" readonly 
          v-on:close="locationOverlay = false" v-on:click.native.stop/>
    </v-overlay>
  </v-card>
</template>


<script>
import axios from 'axios'
import { helper, messages } from '~/mixins/helper'
import { mtproto } from '~/mixins/telegram'
import ShiftCard from '~/components/ShiftCard.vue'
import Leaflet from '~/components/Leaflet.vue'


export default {
  name: 'MyShifts',
  mixins: [helper, messages, mtproto],
  props: {},
  components: {
    ShiftCard,
    Leaflet
  },

  data () {
    return {
      expanded: [],
      shiftOverlay: false,
      locationOverlay: false,
      shift: null,
      schedule: null,
      location: null,
      shifts: [],
      shiftsAll: [],
      allTeams: false,
      headers: [
        { text: this.$t('teams.team_name'), value: 'schedule.team.display_name', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('general.actions'), value: 'view' },
      ],
    }
  },

  computed: {
    mapWidth() {
      var newWidth = this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
      return newWidth
    },

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
    },
    
    showLocationOverlay(shift) {
      this.location = shift.location
      this.locationOverlay = true
    },


    showTradeButton(shift) {
      var result = null
      var shiftUser = shift.users.filter(user => user.id === this.user.id)
      if (shiftUser.length > 0) {
        result = shiftUser[0].pivot.status
      }

      return (shift.schedule.status == 2) && (result > 1)

    },

    async updateTrade (shift) {
      if (shift.pivot.status == 4 || await this.$root.$confirm(this.$t('shifts.confirm_trade_offer'), null, 'info')) {

        const newStatus = (shift.pivot.status == 4) ? 2 : 4


        // If notifications are enabled for this team, send a group message via Telegram
        if (this.notificationsEnabled && newStatus == 4) {
          const message_text = this.message_trade_offer(this.user.name, shift.time_start, shift.time_end, shift.location.name)
          const channel_id = this.team.notificationsettings.telegram_channel_id

          await this.mtInitialize()
          await this.sendMessage(channel_id, message_text)
        }


        await axios({
          method: 'post',      
          url: '/api/schedules/shiftuserstatus',
          data: {
            user_id: this.user.id,
            shift_id: shift.id,
            status: newStatus
          }
        })
        .then(response => {
          this.getShifts()
        })
      }
    },
  }
}

</script>