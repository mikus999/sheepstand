<template>
  <v-card outlined :width="width" elevation="2" >
    <v-card-title>
      <v-icon left>mdi-account-switch</v-icon>
      {{ $t('shifts.status_2a') }}
    </v-card-title>


    <v-stepper v-model="stepper" >
      <v-stepper-items>
        <!-- Trade with all or choose user/shift -->
        <v-stepper-content step="1">
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
        <v-stepper-content step="2">
          <v-card-text>
          </v-card-text>
        </v-stepper-content>

      </v-stepper-items>
    </v-stepper>      

    <v-card-actions>
      <v-spacer />
      <v-btn text @click="$emit('close')">{{ $t('general.cancel')}}</v-btn>
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
    }
  },

  data() {
    return {
      stepper: 1,
    }
  },

  created() {
    
  },

  methods: {
    
    async updateStatus (offerAll) {
      var status = offerAll ? 4 : 5

      if (await this.$root.$confirm(this.$t('shifts.confirm_trade_offer'), null, 'info')) {

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
          this.storeUserShifts(response.data.usershifts)
          this.checkConflictsAllUserShifts()
          this.$emit('updated', response.data.shiftusers)
          this.storeTrades(response.data.trades)
        })

      } 
    },


    async sendNotification() {
      // If notifications are enabled for this team, send a group message via Telegram
      
      if (this.notificationsEnabled && newStatus == 4) {
        const message_text = this.message_trade_offer(this.user.name, this.shift.time_start, this.shift.time_end, this.shift.location.name)
        const channel_id = this.team.notificationsettings.telegram_channel_id

        await this.mtInitialize()
        await this.sendMessage(channel_id, message_text)
      }
      
    }
  },
}
</script>