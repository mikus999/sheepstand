<template>
  <v-card :outlined="!onlyinfo" hover :width="width">
    <v-card-title class="justify-center text-h6" :style="'background-color: ' + (shift.location.color_code !== null ? shift.location.color_code : '')">
      {{ shift.location.name }}
    </v-card-title>

    <v-card-subtitle class="text-center font-weight-bold" :style="'background-color: ' + (shift.location.color_code !== null ? shift.location.color_code : '')">
      <div>{{ $dayjs(shift.time_start).format('ddd, L') }}</div>
      <div>{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</div>
    </v-card-subtitle>


    <v-card-text :style="'overflow-y: auto; height: ' + height">
      <div v-if="onlyinfo" class="text-overline">{{ $t('shifts.participants') }}</div>
      <div v-for="user in shift.users" :key="user.id" class="ma-2" :title="shiftStatus[user.pivot.status].text" disabled>
        <v-icon class="ml-n4 mr-2" :color="shiftStatus[user.pivot.status].color">{{ shiftStatus[user.pivot.status].icon }}</v-icon>
        <span :class="shiftStatus[user.pivot.status].color + '--text'">{{ user.name }}</span>
      </div>

      <div v-for="n in returnZero(shift.max_participants - shift.users.length)" :key="n" class="ma-2" disabled>
        <div class="ml-n5 dashed-border rounded" width="100%">
          <v-icon class="ml-1 mr-2" color="grey">mdi-account-outline</v-icon>
          <span>{{ $t('general.available') }}</span>
        </div>
      </div>
    </v-card-text>

    <v-system-bar :color="$vuetify.theme.dark ? '#1c1c1c' : '#ffffff'">
      <MarqueeText v-if="hasConflicts">
          <span v-for="(conflict, index) in conflicts_user" :key="index" class="warning-text">
            <v-icon color="red" class="ml-6">mdi-alert-box</v-icon>
            {{ getConflictMessage(conflict) }}
          </span>
      </MarqueeText>
    </v-system-bar>

    <v-divider v-if="!onlyinfo" class="ma-0"></v-divider>

    <v-card-actions v-if="!onlyinfo">
      <v-row dense>
        <v-col class="text-center">
          <v-btn icon @click.stop="showLocationOverlay" class="me-2" :disabled="shift.location.map === null">
            <v-icon>mdi-map-search</v-icon>
          </v-btn>
        </v-col>
        <v-col class="text-center">
          <v-btn :disabled="!showButton('request')" :color="(request && myShiftStatus) ? '' : ''" icon @click.stop="updateShiftUser" class='me-2'>
            <v-icon>{{ request ? 'mdi-account-minus' : 'mdi-account-plus' }}</v-icon>
          </v-btn>
        </v-col>
        <v-col class="text-center">
          <v-btn :disabled="!showButton('trade')" :color="trade ? 'blue' : ''" icon @click.stop="updateTrade">
            <v-icon>mdi-account-convert</v-icon>
          </v-btn>
        </v-col>
      </v-row>
    </v-card-actions>
    
    <v-card-actions v-else>
      <v-spacer></v-spacer>
      <v-btn color="primary" text v-on:click="$emit('close')">
        {{ $t('general.close' ) }}
      </v-btn>
    </v-card-actions>


    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
      <Leaflet :location="shift.location" :fill="shift.location.color_code" :width="mapWidth" height="500px" readonly 
          v-on:close="locationOverlay = false" v-on:click.native.stop/>
    </v-overlay>
  </v-card>
</template>


<script>
import axios from 'axios'
import { helper, scheduling, messages } from '~/mixins/helper'
import mtproto from '~/mixins/telegram'
import Leaflet from '~/components/Leaflet.vue'
import MarqueeText from 'vue-marquee-text-component'

export default {
  name: 'ShiftCard',
  mixins: [helper, scheduling, messages, mtproto],
  components: {
    Leaflet,
    MarqueeText
  },
  props: {
    shift: {
      type: [Object, Array]
    },
    schedule: {
      type: [Object, Array]
    },
    user_shifts: {
      type: [Object, Array],
      default: null
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '170px'
    },
    onlyinfo: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      request: false,
      trade: false,
      locationOverlay: false,
      conflicts_user: [],
      user_shifts_mutable: [],
    }
  },

  created () {
    this.request = this.isShiftMember
    this.trade = this.myShiftStatus == 4
    this.user_shifts_mutable = this.user_shifts

    // Checks for possible conflicts with user's existing shift assignements in all teams
    this.conflicts_user = this.checkShiftConflicts(this.shift, this.user_shifts_mutable)
  },

  computed: {
    isShiftMember() {
      var temp = this.shift.users.map(o => o['id'])
      var index = temp.indexOf(this.user.id)
      return index > -1
    },

    hasRequestedTrade() {
      var result = this.myShiftStatus == 4
      return result
    },

    myShiftStatus() {
      var result = null
      var shiftUser = this.shift.users.filter(user => user.id === this.user.id)
      if (shiftUser.length > 0) {
        result = shiftUser[0].pivot.status
      }
      return result
    },

    shiftFull() {
      var result = false
      var autoApproval = this.team.setting_shift_request_autoapproval == 1

      if (autoApproval) {
        var userCount = this.shift.users.length
        result = userCount >= this.shift.max_participants
      }

      return result
    },

    mapWidth() {
      var newWidth = this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
      return newWidth
    },

    hasConflicts() {
      return this.conflicts_user.length > 0
    }
  },

  methods: {
    showButton(btn) {
      var show = false

      switch (btn) {
        case 'request':
          show = (this.schedule.status == 1 && (!this.shiftFull || this.request)) || (this.schedule.status == 2 && (this.myShiftStatus == 0 || this.myShiftStatus == 1))
          break;

        case 'trade':
          show = (this.schedule.status == 2) && (this.myShiftStatus > 1)
          break;

      }

      return show
    },


    dayOfWeek (daynum) {
      var days = []
      days = this.$dayjs().localeData().weekdaysShort()
      days[7] = days[0]
      return days[daynum]
    },


    async updateShiftUser () {
      var status = this.team.setting_shift_request_autoapproval == 1 ? 2 : 1
      var url = null
      this.request = !this.request
      
      if (this.request) {
        url = '/api/schedules/joinshift'
      } else {
        url = '/api/schedules/leaveshift'
      }

      await axios({
        method: 'post',      
        url: url,
        data: {
          user_id: this.user.id,
          shift_id: this.shift.id,
          status: status
        }
      })
      .then(response => {
        this.shift.users = response.data.shiftusers
        this.user_shifts_mutable = response.data.usershifts
        this.conflicts_user = this.checkShiftConflicts(this.shift, this.user_shifts_mutable)
      })

    },

    showLocationOverlay() {
      this.locationOverlay = true
    },

    getConflictMessage(conflict) {
      var result = ''

      if (conflict.type == 'conflict') {
        result += this.$t('shifts.conflict_shift_another_team')
        this.team.display_name == conflict.team ? '' : result += ' (' + conflict.team + ')'
      } else if (conflict.type == 'adjacent') {
        result += this.$t('shifts.conflict_shift_adjacent')
        this.team.display_name == conflict.team ? '' : result += ' (' + conflict.team + ')'
      }

      return result
    },

    async updateTrade () {
      if (this.trade || await this.$root.$confirm(this.$t('shifts.confirm_trade_offer'), null, 'info')) {
        var status = null
        this.trade = !this.trade

        const newStatus = (this.myShiftStatus == 4) ? 2 : 4


        // If notifications are enabled for this team, send a group message via Telegram
        if (this.notificationsEnabled && newStatus == 4) {
          const message_text = this.message_trade_offer(this.user.name, this.shift.time_start, this.shift.time_end, this.shift.location.name)
          const channel_id = this.team.notificationsettings.telegram_channel_id

          await this.mtInitialize()
          await this.sendMessage(channel_id, message_text)
        }


      
        await axios({
          method: 'post',      
          url: '/api/schedules/shiftuserstatus',
          data: {
            user_id: this.user.id,
            shift_id: this.shift.id,
            status: newStatus
          }
        })
        .then(response => {
          this.shift.users = response.data.shiftusers
        })
        
      }
    },

    returnZero (n) {
      return n < 0 ? 0 : n
    }

  }

}
</script>

<style scoped>
  .dashed-border 
  {
    border-style: dashed;
    border-color: 'grey';
    border-width: thin;
  }

  .warning-text {
    font-size: .75rem;
    font-weight: bold;
    color: #ff0000;
    text-transform: uppercase;
  }

</style>