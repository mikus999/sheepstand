<template>
  <v-card outlined :width="width" elevation="2" min-width="300">
    <v-card-title>
      <v-icon left>mdi-account-switch</v-icon>
      {{ $t('shifts.status_2a') }}
    </v-card-title>


    <v-stepper v-model="stepper">
      <v-stepper-items>
        <!-- Trade with all or choose user/shift -->
        <v-stepper-content step="1" class="ma-0 pa-0">
          <v-card-text class="text-center">
            <v-row class="text-center">
              <v-col cols=12 class="text-center">
                <v-btn
                  block
                  color="primary"
                  @click="updateStatus(true)"
                >
                  <v-icon left>mdi-account-group</v-icon>
                  <span>{{ $t('shifts.trade_offer_all') }}</span>
                </v-btn>

                <v-subheader v-if="this.notificationsEnabled" class="mt-4 justify-center">
                  {{ $t('shifts.trade_with_notification') }}
                </v-subheader>
              </v-col>
            </v-row>

            <v-divider class="my-12" />

            <v-row>
              <v-col cols=12 class="text-center">
                <v-btn
                  block
                  @click="stepper = 2"
                >
                  <v-icon left>mdi-account</v-icon>
                  <span>{{ $t('shifts.trade_choose_person') }}</span>
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-stepper-content>



        <!-- Choose user/shift -->
        <v-stepper-content step="2" class="ma-0 pa-0">
          <v-card-text>
            <v-select
              v-model="selectedUser"
              return-object
              :items="teamUsers"
              item-value="id"
              item-text="name"
              :label="$t('shifts.trade_choose_person')"
              @change="getUserShifts"
            >
            </v-select>



            <v-list :style="'overflow-y: auto; height: ' + height">
              <v-list-item-group 
                v-model="selectedShift"
                v-if="selectedUser != null"
                color="primary"
              >
                <v-list-item
                  v-for="shift in userShifts"
                  :key="shift.id"
                  :value="shift"
                >
                  <v-list-item-avatar>
                    <v-avatar :color="shift.location.color_code" class="location-avatar">
                      {{ shift.location.name.substring(0, 1) }}
                    </v-avatar>
                  </v-list-item-avatar>

                  <v-list-item-content>
                    <v-list-item-title class="text-left list-title">
                      {{ shift.time_start | formatDay }} {{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}
                    </v-list-item-title>

                    <v-list-item-subtitle class="text-left list-title">
                      {{ shift.location.name }}
                    </v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-card-text>
        </v-stepper-content>

      </v-stepper-items>
    </v-stepper>      

    <v-card-actions>
      <v-spacer />
      <v-btn text @click="$emit('close')">{{ $t('general.cancel')}}</v-btn>

      <v-btn color="primary" v-if="selectedShift != null" @click="updateStatus(false)">{{ $t('shifts.trade_offer') }}</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling, messages } from '~/mixins/helper'
import { mtproto } from '~/mixins/telegram'

export default {
  name: 'ShiftTrade',
  mixins: [helper, scheduling, messages, mtproto],

  props: {
    shift: {
      type: [Object, Array]
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '250px'
    },
  },

  data() {
    return {
      stepper: 1,
      teamUsers: [],
      userShifts: [],
      selectedUser: null,
      selectedShift: null,
    }
  },

  created() {
    this.getTeamUsers()
  },

  methods: {
        
    async getTeamUsers() {
      await axios.get('/api/teams/' + this.shift.schedule.team_id + '/users/')
        .then(response => {
          this.teamUsers = response.data.filter(u => 
            u.id != this.user.id &&
            u.shifts.length > 0 &&
            u.shifts.filter(s => s.pivot.status == 2).length > 0
          )
        })
    },


    async getUserShifts() {
      if (this.selectedUser != null) {
        await axios({
          method: 'post',      
          url: '/api/user/shifts/',
          data: {
            user_id: this.selectedUser.id,
            team_id: this.shift.schedule.team_id,
          }
        })
        .then(response => {
          this.userShifts = response.data
        })
      }
    },


    async updateStatus (offerAll) {
      var status = offerAll ? 4 : 5

      if (await this.$root.$confirm(this.$t('shifts.confirm_trade_offer'), null, 'info')) {

        await axios({
          method: 'post',      
          url: '/api/schedules/shiftuserstatus',
          data: {
            user_id: this.user.id,
            shift_id: this.shift.id,
            trade_user_id: this.selectedUser != null ? this.selectedUser.id : null,
            trade_shift_id: this.selectedUser != null ? this.selectedShift.id : null,
            status: status
          }
        })
        .then(response => {
          this.storeUserShifts(response.data.usershifts)
          this.checkConflictsAllUserShifts()
          this.$emit('updated', response.data.shiftusers)
          this.storeTrades(response.data.trades)
          this.sendNotification(status)
          this.createMessage(status)

          var success_msg = offerAll ? this.$t('shifts.success_trade_offered') : this.$t('shifts.success_trade_offered_publisher')
          this.showSnackbar(success_msg, 'success')
          this.$emit('close')

        })

      } 
    },


    async sendNotification(status) {
      /**
       * Send a group message via Telegram if... 
       *  - notifications are enabled for this team
       *  - offering trade to all
       *  */ 
      
      const team = this.shift.schedule.team
      const hasNotifications = (team.notificationsettings != null && team.notificationsettings.telegram_channel_id != null)


      if (hasNotifications && status == 4) {
        const message_text = this.message_trade_offer(this.user.name, this.shift.time_start, this.shift.time_end, this.shift.location.name, team.language, true)
        const channel_id = team.notificationsettings.telegram_channel_id

        await this.mtInitialize()
        await this.sendMessage(channel_id, message_text)
      }
      
    },



    async createMessage(status) {
      const team = this.shift.schedule.team
      var expires_date = this.shift.time_start

      if (status == 4) {
        // Send message to team
        var s_id = this.user.id
        var s_type = 'User'
        var r_id = this.shift.schedule.team_id
        var r_type = 'Team'
        var message_subject = this.$t('system_messages.new_trade_offer_subject')
        var message_body = this.message_trade_offer(this.user.name, this.shift.time_start, this.shift.time_end, this.shift.location.name, team.language, true)
      } else {
        // Send message to user
        var s_id = this.user.id
        var s_type = 'User'
        var r_id = this.selectedUser.id
        var r_type = 'User'
        var message_subject = this.$t('system_messages.new_trade_offer_subject')
        var message_body = this.message_user_trade_offer(this.shift, this.selectedShift, team.language, true)
      }

      const message = {
        sender_id: s_id,
        sender_type: s_type,
        recipient_id: r_id,
        recipient_type: r_type,
        message_subject: message_subject,
        message_body: message_body,
        expires_on: expires_date
      }

      this.send_message_user_inbox(message)
      
    },
  },
}
</script>

<style scoped>
  .location-avatar
  {
    font-size: 1.5rem;
  }

  .list-title
  {
    font-size: 0.9rem;
  }
</style>