<template>
  <!-- If user is not a shift member -->
  <div v-if="!isShiftMember">
    <v-btn fab color="primary" @click="joinLeaveShift('join')">
      <v-icon>mdi-account-plus</v-icon>
    </v-btn>
  </div>


  <!-- If user acceptance is pending -->
  <div v-else-if="myShiftStatus == 0">
    <v-btn fab color="primary" @click="updateStatus($t('shifts.confirm_accept'), 2)" class="mx-4">
      <v-icon>mdi-thumb-up</v-icon>
    </v-btn>

    <v-btn fab color="secondary" @click="updateStatus($t('shifts.confirm_accept'), 3)" class="mx-4">
      <v-icon>mdi-thumb-down</v-icon>
    </v-btn>
  </div>


  <!-- If user has requested a shift -->
  <div v-else-if="myShiftStatus == 1">
    <v-btn fab color="primary" @click="joinLeaveShift('leave')">
      <v-icon>mdi-account-minus</v-icon>
    </v-btn>
  </div>


  <!-- If user is accepted/approved -->
  <div v-else-if="myShiftStatus == 2">
    <v-btn v-if="!isFinalized" fab color="primary" @click="joinLeaveShift('leave')">
      <v-icon>mdi-account-minus</v-icon>
    </v-btn>

    
  </div>
</template>

<script>
import axios from 'axios'
import { helper, scheduling, messages } from '~/mixins/helper'
import mtproto from '~/mixins/telegram'


export default {
  name: 'ShiftStatusButton',
  mixins: [helper, scheduling, messages, mtproto],
  props: {
    shift: {
      type: [Object, Array]
    },
  },

  data() {
    return {
      request: false,
      trade: false,
    }
  },


  computed: {
    isShiftMember() {
      var temp = this.shift.users.map(o => o['id'])
      var index = temp.indexOf(this.user.id)
      return index > -1
    },

    isFinalized() {
      return this.shift.schedule.status == 2
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

  created() {
    this.request = this.isShiftMember
    this.trade = this.hasRequestedTrade
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


    async joinLeaveShift (action) {
      var url = null
      var status = null

      if (action == 'join') {
        status = this.team.setting_shift_request_autoapproval == 1 ? 2 : 1
        url = '/api/schedules/joinshift'
      } else {
        status = 0
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


    async updateStatus (confirm_msg, status) {
      if (this.trade || await this.$root.$confirm(confirm_msg, null, 'info')) {


        // If notifications are enabled for this team, send a group message via Telegram
        /*
        if (this.notificationsEnabled && newStatus == 4) {
          const message_text = this.message_trade_offer(this.user.name, this.shift.time_start, this.shift.time_end, this.shift.location.name)
          const channel_id = this.team.notificationsettings.telegram_channel_id

          await this.mtInitialize()
          await this.sendMessage(channel_id, message_text)
        }
        */

      
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
        
      }
    },

  },
}
</script>

<style scoped>

</style>