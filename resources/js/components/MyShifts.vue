<template>
  <v-card>
    <v-data-table :headers="headers" :items="filteredShifts || []" disable-sort width="100%">
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>mdi-calendar-account</v-icon>
            {{ $t('shifts.my_shifts') }}
          </v-toolbar-title>


          <v-spacer></v-spacer>

          <v-btn 
            color="secondary" 
            v-if="$vuetify.breakpoint.smAndUp"
            :to="{ name: 'schedules.shifts' }"
          >
            <v-icon left small>mdi-calendar</v-icon>
            {{ $t('shifts.see_more') }}
          </v-btn>
        </v-toolbar>

        <v-switch v-model="allTeams" :label="$t('shifts.show_all_teams')"  hide-details class="mx-4" />

      </template>

      <template v-slot:item.team_name="{ item }">
        <v-btn icon @click="showShiftOverlay(item)">
          <v-icon>mdi-card-account-details-outline</v-icon>
        </v-btn>
        {{ item.schedule.team.display_name }}
      </template>

      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}<br>
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
      </template>

      <template v-slot:item.location="{ item }">
        <v-chip label small :color="item.location.color_code" @click="showLocationOverlay(item)">{{ item.location.name }}</v-chip>
      </template>

      <template v-slot:item.view="{ item }">
        <ShiftStatusButton :shift="item" table-actions />
      </template>

    </v-data-table>

    <v-overlay :value="shiftOverlay" @click.native="shiftOverlay = false" :dark="theme=='dark'">
      <ShiftCard 
        :shift="shift" 
        onlyinfo 
        width="300px" 
        height="100%" 
        v-on:close="shiftOverlay = false"
        v-on:click.native.stop
      />
    </v-overlay>

    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
      <Leaflet 
        :location="location" 
        :width="mapWidth" 
        height="500px" 
        readonly 
        v-on:close="locationOverlay = false" 
        v-on:click.native.stop 
      />
    </v-overlay>
  </v-card>
</template>


<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, messages, scheduling } from '~/mixins/helper'
import { mtproto } from '~/mixins/telegram'
import ShiftCard from '~/components/ShiftCard.vue'
import Leaflet from '~/components/Leaflet.vue'
import ShiftStatusButton from '~/components/ShiftStatusButton.vue'


export default {
  name: 'MyShifts',
  mixins: [helper, messages, scheduling, mtproto],
  props: {},
  components: {
    ShiftCard,
    Leaflet,
    ShiftStatusButton
  },

  data () {
    return {
      expanded: [],
      shiftOverlay: false,
      locationOverlay: false,
      shift: null,
      location: null,
      allTeams: true,
      headers: [
        { text: this.$t('teams.team_name'), value: 'team_name', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('general.actions'), value: 'view', align: 'center' },
      ],
    }
  },

  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      user_shifts: 'scheduling/user_shifts',
    }),

    mapWidth() {
      var newWidth = this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
      return newWidth
    },

    filteredShifts() {
      if (this.allTeams) {
          return this.user_shifts
      } else {
          return this.user_shifts.filter(shift => shift.schedule.team_id === this.team.id)
      }
    }
  },


  created () {
    this.getShifts()
  },

  methods: {
    async getShifts () {
      await axios.get('/api/user/shifts')
        .then(response => {
          this.storeUserShifts(response.data)

        })
    },


    showShiftOverlay(shift) {
      this.shift = shift
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
            this.storeUserShifts(response.data.usershifts)
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
        this.storeUserShifts(response.data.usershifts)
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
          this.storeUserShifts(response.data.usershifts)
        })
      }
    },
  }
}

</script>