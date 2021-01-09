<template>
  <v-card outlined class="ma-6">
    <v-card-title>
      <v-icon left>{{ icons.mdiTelegram }}</v-icon>
      {{ $t('notifications.notifications')}}
    </v-card-title>

    <v-card-text>
      <div>
        <p>
          <span>{{ $t('general.status') }}: </span>
          <v-icon v-if="notificationsEnabled" small color="success">{{ icons.mdiCheckboxMarkedCircle }}</v-icon>
          <v-icon v-else small color="red">{{ icons.mdiCloseCircle }}</v-icon>
          <span class="font-weight-bold">{{ notificationsEnabled ? $t('general.enabled') : $t('general.disabled') }}</span>
        </p>
      </div>

      <!-- If notifications are setup and working properly -->
      <div v-if="notificationsEnabled && !chatError">
        <p><span>{{ $t('notifications.group_name')}}: </span><span class="font-weight-bold">{{ chatInfo.title }}</span></p>
        <p><span>{{ $t('notifications.group_description')}}: </span><span class="font-weight-bold">{{ chatInfo.description }}</span></p>
        <p>
          <span>{{ $t('notifications.invite_link')}}: </span>
          <span class="font-weight-bold">{{ chatInfo.invite_link }}</span>
          <v-btn @click="copyText(chatInfo.invite_link)" icon>
            <v-icon small>{{ icons.mdiContentCopy }}</v-icon>
          </v-btn>
        </p>
        <v-btn text block color="secondary" class="my-2" @click="disableNot()">{{ $t('notifications.disable_notifications') }}</v-btn>
      </div>

      <!-- If notifications have not been setup -->
      <div v-else-if="!notificationsEnabled && !chatError">
        <p><span>{{ $t('notifications.feature_explanation_team') }}</span></p>
        <v-btn :to="{ name: 'notifications.setup' }" block color="primary" class="my-2">{{ $t('notifications.setup_now') }}</v-btn>
        <v-btn text block color="secondary" class="my-2" @click="disableNot()">{{ $t('notifications.disable_notifications') }}</v-btn>
      </div>

      <!-- If notifications are setup but there was a problem retrieving chat details -->
      <div v-else-if="notificationsEnabled && chatError">
        <p><span>{{ $t('general.error')}}: </span><span class="font-weight-bold red--text">{{ $t('notifications.notifications_problem_admin') }}</span></p>
        <v-btn :to="{ name: 'notifications.setup' }" block color="primary" class="my-2">{{ $t('notifications.setup_now') }}</v-btn>
        <v-btn text block color="secondary" class="my-2" @click="disableNot()">{{ $t('notifications.disable_notifications') }}</v-btn>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import mtproto from '~/mixins/telegram'

export default {
  name: 'NotificationInfo',
  mixins: [helper, mtproto],

  data() {
    return {
      chatInfo: {
        title: null,
        description: null,
        invite_link: null
      },
      chatError: false,
    }
  },

  created() {
    this.getNotificationInfo()
  },

  methods: {
    async getNotificationInfo() {

      if (this.notificationsEnabled) {
        // Initialize the mtproto object
        this.mtInitialize()
          .then(result => {
            const chat_id = '-100' + this.team.notificationsettings.telegram_channel_id
            const url = this.bot_api_base + 'getChat?chat_id=' + chat_id

            // Execute bot api calls
            axios.get(url)
            .then(response => {
              this.chatInfo = response.data.result
              this.chatError = false

              if (this.chatInfo.invite_link == null || this.chatInfo.invite_link == '' || this.chatInfo.invite_link == undefined) {
                this.setGroupLink(this.team.notificationsettings.telegram_channel_id)
                  .then(link => {
                    this.chatInfo.invite_link = link
                  })
              }
            })
            .catch(error => {
              this.chatInfo = null
              this.chatError = true
            })
        })

        
      } 
    },


    disableNot() {
      this.disableNotifications()
      this.$emit('updated')
    },
  },


}
</script>