<template>
  <div v-if="isExpired || (isFinalized && !isShiftMember)">
    <v-icon 
      :large="!tableActions"
    >
      mdi-lock
    </v-icon>
  </div>

  <div v-else>
    <!-- If user is not a shift member -->
    <div v-if="!isShiftMember && !isFinalized && !isShiftFull">
      <v-btn 
        :fab="!tableActions" 
        :icon="tableActions" 
        :loading="loading"
        color="primary" 
        @click.stop="joinLeaveShift('join')"
      >
        <v-icon>mdi-account-plus</v-icon>
      </v-btn>
    </div>


    <!-- If user acceptance is pending -->
    <div v-else-if="myShiftStatus == 0">
      <v-btn 
        :fab="!tableActions" 
        :icon="tableActions"  
        :loading="loading"
        color="secondary"
        @click.stop="updateStatus($t('shifts.confirm_accept'), 2)" 
      >
        <v-icon color="green" :small="tableActions">mdi-thumb-up</v-icon>
      </v-btn>

      <v-btn 
        :fab="!tableActions" 
        :icon="tableActions" 
        :loading="loading"
        color="secondary"
        @click.stop="updateStatus($t('shifts.confirm_reject'), 3)" 
      >
        <v-icon color="red" :small="tableActions">mdi-thumb-down</v-icon>
      </v-btn>
    </div>


    <!-- If user has requested a shift -->
    <div v-else-if="myShiftStatus == 1">
      <v-btn 
        :fab="!tableActions" 
        :icon="tableActions" 
        :loading="loading"
        color="secondary" 
        @click.stop="joinLeaveShift('leave')"
      >
        <v-icon>mdi-account-minus</v-icon>
      </v-btn>
    </div>


    <!-- If status is accepted/approved -->
    <div v-else-if="myShiftStatus == 2">
      <v-btn 
        v-if="!isFinalized" 
        :fab="!tableActions" 
        :icon="tableActions"  
        :loading="loading"
        color="secondary" 
        @click.stop="joinLeaveShift('leave')"
      >
        <v-icon>mdi-account-minus</v-icon>
      </v-btn>

      <v-btn 
        v-else 
        :fab="!tableActions" 
        :icon="tableActions"  
        :loading="loading"
        color="primary" 
        @click.stop="tradeOverlay = true"
      >
        <v-icon :color="tableActions ? 'secondary' : ''">mdi-account-switch</v-icon>
      </v-btn>
    </div>


    <!-- If status is rejected/refused -->
    <div v-else-if="myShiftStatus == 3 && !isFinalized && !isShiftFull">
      <v-btn 
        :fab="!tableActions" 
        :icon="tableActions"  
        :loading="loading"
        color="primary" 
        @click.stop="joinLeaveShift('join')"
      >
        <v-icon>mdi-account-plus</v-icon>
      </v-btn>
    </div>



    <!-- If trade offer is pending -->
    <div v-else-if="myShiftStatus == 4 || myShiftStatus == 5">
      <v-btn 
        :fab="!tableActions" 
        :icon="tableActions" 
        :loading="loading"
        color="secondary" 
        @click.stop="updateStatus($t('shifts.confirm_trade_cancel'), 2)"
      >
        <v-icon color="primary">mdi-account-switch</v-icon>
      </v-btn>
    </div>


    <v-overlay :value="tradeOverlay" @click.stop.native="tradeOverlay = false" :dark="theme=='dark'">
      <ShiftTrade 
        :shift="shift" 
        v-on:close="tradeOverlay = false" 
        v-on:updated="tradeUpdate"
        v-on:click.native.stop 
      />
    </v-overlay>

  </div>

</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling, messages } from '~/mixins/helper'
import mtproto from '~/mixins/telegram'
import ShiftTrade from '~/components/ShiftTrade.vue'


export default {
  name: 'ShiftStatusButton',
  mixins: [helper, scheduling, messages, mtproto],
  components: {
    ShiftTrade
  },

  props: {
    shift: {
      type: [Object, Array]
    },
    tableActions: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      request: false,
      trade: false,
      loading: false,
      fab0: false,
      tradeOverlay: false
    }
  },


  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      user_shifts: 'scheduling/user_shifts',
      shifts_available: 'scheduling/shifts_available'
    }),
    
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

    isShiftFull() {
      var result = false
      var autoApproval = this.team.setting_shift_request_autoapproval == 1

      if (autoApproval) {
        var users = this.shift.users.filter(u => u.pivot.status != 3)
        var userCount = users.length
        result = userCount >= this.shift.max_participants
      }

      return result
    },

    isExpired() {
      return this.$dayjs(this.shift.time_end).isBefore(this.$dayjs())
    }
  },

  created() {
    this.trade = this.hasRequestedTrade
  },

  methods: {

    async joinLeaveShift (action) {
      this.loading = true

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
        this.storeUserShifts(response.data.usershifts)
        this.storeTrades(response.data.trades)
        this.checkConflictsAllUserShifts()
        this.$emit('updated')
        this.loading = false
      })
      .catch(error => {
        this.loading = false
      })

    },


    async updateStatus (confirm_msg, status) {
      this.loading = true

      if (this.trade || await this.$root.$confirm(confirm_msg, null, 'info')) {
      
        await axios({
          method: 'post',      
          url: '/api/schedules/shiftuserstatus',
          data: {
            user_id: this.user.id,
            shift_id: this.shift.id,
            trade_user_id: null,
            trade_shift_id: null,
            status: status
          }
        })
        .then(response => {
          this.shift.users = response.data.shiftusers
          this.storeUserShifts(response.data.usershifts)
          this.storeTrades(response.data.trades)
          this.checkConflictsAllUserShifts()
          this.$emit('updated')
          this.loading = false
        })
        .catch(error => {
          this.loading = false
        })
      } else {
        this.loading = false
      }
    },


    tradeUpdate(value) {
      //this.tradeOverlay = false
      this.shift.users = value
    }
  },
}
</script>
