<template>
  <v-card outlined hover :width="width" :style="'background-color: ' + background">
    <v-card-title class="justify-center" :style="'background-color: ' + (shift.location.color_code !== null ? shift.location.color_code : '')">
      {{ shift.location.name }}
    </v-card-title>

    <v-card-subtitle class="text-center" :style="'background-color: ' + (shift.location.color_code !== null ? shift.location.color_code : '')">
      {{ dayOfWeek($dayjs(shift.time_start).isoWeekday()) }}
      {{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}
    </v-card-subtitle>

    <v-card-text :style="'overflow-y: auto; height: ' + height">
        <div v-for="user in shift.users" :key="user.id" class="ma-2" disabled>
          <v-icon class="ml-n4 mr-2" :color="shiftStatus[user.pivot.status].color">{{ shiftStatus[user.pivot.status].icon }}</v-icon>
          <span :class="shiftStatus[user.pivot.status].color + '--text'">{{ user.name }}</span>
        </div>
    </v-card-text>

    <v-divider class="mx-3"></v-divider>

    <v-card-actions class="justify-center">
      <v-btn :disabled="!showButton('request')" :color="(request && myShiftStatus) ? shiftStatus[myShiftStatus].color : ''" icon @click.stop="updateShiftUser">
        <v-icon>mdi-hand-right</v-icon>
      </v-btn>

      <v-btn :disabled="!showButton('trade')" :color="trade ? 'blue' : ''" icon @click.stop="updateTrade">
        <v-icon>mdi-account-switch</v-icon>
      </v-btn>
    </v-card-actions>
  </v-card>
</template>


<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'ShiftCard',
  mixins: [helper],
  props: {
    shift: {
      type: Object
    },
    schedule: {
      type: Object
    },
    background: {
      type: String,
      default: ''
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '150px'
    },
  },

  data () {
    return {
      request: false,
      trade: false
    }
  },

  created () {
    this.request = this.isShiftMember
    this.trade = this.myShiftStatus == 4
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
  },

  methods: {
    showButton(btn) {
      var show = false

      switch (btn) {
        case 'request':
          show = (this.schedule.status == 1) && !this.shiftFull
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
      })

    },


    async updateTrade () {
      var status = null
      this.trade = !this.trade

      if (this.trade) {
        status = 4
      } else {
        status = 2
      }


      await axios({
        method: 'post',      
        url: '/api/schedules/shiftuserstatus',
        data: {
          user_id: this.user.id,
          shift_id: this.shift.id,
          status: status
        }
      })
      .then(response => {
        this.shift.users = response.data.shiftusers
      })
    },

  }

}
</script>