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
        <v-btn icon @click="showShiftOverlay(item)">
          <v-icon>mdi-card-account-details-outline</v-icon>
        </v-btn>

        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn 
              icon 
              v-if="!showTradeButton(item)" 
              :color="shiftStatus[item.pivot.status].color" 
              @click.stop="updateStatus(item)"
              v-bind="attrs"
              v-on="on"
            >
              <v-icon>{{ shiftStatus[item.pivot.status].icon }}</v-icon>
            </v-btn>
          </template>
          <span>{{ shiftStatus[item.pivot.status].text }}</span>
        </v-tooltip>

        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn 
              icon  
              v-if="showTradeButton(item)" 
              :color="item.pivot.status == 4 ? 'blue' : ''" 
              @click.stop="updateTrade(item)"
              v-bind="attrs"
              v-on="on"
            >
              <v-icon>mdi-account-convert</v-icon>
            </v-btn>
          </template>
          <span>{{ item.pivot.status == 4 ? $t('shifts.status_4') : $t('shifts.status_2a') }}</span>
        </v-tooltip>       
      </template>

    </v-data-table>

    <v-overlay :value="shiftOverlay" :dark="theme=='dark'">
      <ShiftCard :shift="shift" :schedule="schedule" :user_shifts="shiftsAll" onlyinfo width="300px" height="100%" v-on:close="shiftOverlay = false"></ShiftCard>
    </v-overlay>

    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
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

    async updateStatus(shift) {
      var newStatus = 0
      var confirm_msg = null
      var success_msg = null

      if (shift.pivot.status == 0) {
        newStatus = 2
        confirm_msg = this.$t('shifts.confirm_trade')
      } else if (shift.pivot.status == 1) {
        newStatus = 9
        confirm_msg = this.$t('shifts.confirm_remove_request')
      } else if (shift.pivot.status == 2 && shift.schedule.status != 2) {
        newStatus = 9
        confirm_msg = this.$t('shifts.confirm_remove_shift')
      } else {
        console.log(shift)
      }



      if (await this.$root.$confirm(confirm_msg, null, 'info')) {
        if (newStatus == 9) {
          this.leaveShift(shift)

        } else {
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
      }
    },


    async leaveShift (shift) {
      var url = '/api/schedules/leaveshift'      

      await axios({
        method: 'post',      
        url: url,
        data: {
          user_id: this.user.id,
          shift_id: shift.id
        }
      })
      .then(response => {
          this.getShifts()
      })

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