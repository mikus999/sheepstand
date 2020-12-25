<template>
  <v-card width="100%">
    <v-data-table 
      :headers="headers" 
      :items="filteredShifts || []"  
      disable-sort
      :hide-default-header="$vuetify.breakpoint.xs" 
      width="100%"
      @click:row="showShiftOverlay"
    >
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


      <!-- MOBILE VIEW -->
      <template v-slot:body="{ items }" v-if="$vuetify.breakpoint.xs">
        <v-expansion-panels class="mt-3">
          <v-expansion-panel
            v-for="item in items"
            :key="item.id"
          >
            <v-expansion-panel-header class="py-0" disable-icon-rotate>
              <v-col cols="2" class="pa-0">
                <v-btn fab small class="location-avatar" :color="item.location.color_code" @click.stop="showLocationOverlay(item)">
                  {{ item.location.name.substring(0, 1) }}
                </v-btn>
              </v-col>

              <v-col class="shift-subtitle">
                <div class="shift-title mb-2">
                  {{ item.time_start | formatDay }}<br>
                  {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}<br>
                </div>
                {{ item.location.name }}
              </v-col>

              <template v-slot:actions>
                <v-icon :color="shiftStatus[item.pivot.status].color">
                  {{ shiftStatus[item.pivot.status].icon }}
                </v-icon>
              </template>
            </v-expansion-panel-header>

            <v-expansion-panel-content>
              <div class="shift-subtitle">
                <span class="text-overline">{{ $t('teams.team_name') }}: </span>
                {{ item.schedule.team.display_name }}
              </div>

              <div class="text-overline">{{ $t('shifts.participants') }}</div>

              <div v-for="user in filterShiftUsers(item.users)" :key="user.id" class="ma-2 list-participants" :title="shiftStatus[user.pivot.status].text" disabled>
                <v-icon small class="ml-n2 mr-2" :color="shiftStatus[user.pivot.status].color">
                  {{ shiftStatus[user.pivot.status].icon }}
                </v-icon>
                <span :class="(shiftStatus[user.pivot.status].color + '--text ') + (user.pivot.status == 3 ? 'text-decoration-line-through' : '')">
                  {{ user.name }}
                </span>
              </div>

              <v-row>
                <v-col class="text-center">
                  <ShiftStatusButton :shift="item" />
                </v-col>
              </v-row>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </template>



      <template v-slot:item.team_name="{ item }">
        {{ item.schedule.team.display_name }}
      </template>

      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}<br>
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
      </template>

      <template v-slot:item.location="{ item }">
        <v-btn fab x-small class="location-avatar-xs mr-2" :color="item.location.color_code" @click.stop="showLocationOverlay(item)">
          {{ item.location.name.substring(0, 1) }}
        </v-btn>
        <span>{{ item.location.name }}</span>
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
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('teams.team_name'), value: 'team_name', align: 'left' },
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

    filterShiftUsers(shiftUsers) {
      return shiftUsers.filter(u => u.pivot.status != 3)
    },
  }
}

</script>


<style scoped>
  .location-avatar
  {
    font-size: 1.5rem;
  }

  .location-avatar-xs
  {
    font-size: 1.2rem;
  }

  .shift-title
  {
    font-size: .9rem !important;
    font-weight: bold;
    line-height: 1.25;
  }

  .shift-subtitle
  {
    font-size: .8rem !important;
  }

  .list-participants
  {
    font-size: .75rem;
  }

</style>