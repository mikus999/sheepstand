<template>
<v-card class="mx-auto" :max-width="width" outlined>
  <v-card-title>
    <v-icon left>mdi-telegram</v-icon>
    Enable Notifications
  </v-card-title>

  <v-card-text>
    <v-stepper v-model="step">
      <v-stepper-items>
        <v-stepper-content step="1">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">Notifications using Telegram</h3>
              <p class="text-muted my-8">
                {{ $t('notifications.feature_explanation_user') }}
              </p>
              <p class="text-muted my-8">
                SheepStand uses Telegram for notifications.
                Telegram is a free and secure messaging app, similar to Viber and WhatsApp.
                If you do not already have a Telegram account, you can download the app from the link below and sign up.
              </p>
              <p class="my-8">
                <DownloadTelegram />
              </p>
              <p class="text-muted my-8">
                After signing up for an account, or if you are already a Telegram user, continue to the next step.
              </p>

              <v-btn color="primary" @click="step = 2">I have Telegram</v-btn>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="2">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">Scan or Click to Join</h3>
              <p class="text-muted my-8">
                Scan or click the QR Code below using the device where Telegram is installed.
              </p>

              <div class="my-16 text-center">
                <a :href="qr_value" target="_blank" class="text-decoration-none">
                  <qrcode-vue :value="qr_value" :size="qr_size" level="H" class="mx-auto"></qrcode-vue>
                </a>
              </div>

              <v-btn text @click="cancelSetup">Cancel</v-btn>
            </v-col>
          </v-row>
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </v-card-text>
</v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import mtproto from '~/mixins/telegram'
import DownloadTelegram from '~/components/DownloadTelegram'
import QrcodeVue from 'qrcode.vue'

export default {
  name: 'NotificationJoin',
  mixins: [helper, mtproto],
  components: {
    DownloadTelegram,
    QrcodeVue
  },
  props: {
    width: {
      type: Number,
      default: 600
    }
  },

  data() {
    return {
      step: 1,
      qr_value: 'https://telegram.org',
      qr_size: 300,
    }
  },

  created() {
    this.mtInitialize()
    this.initializeQRCode()
  },

  methods: {
    async initializeQRCode() {
      await this.getGroupLink()
        .then(result => {

          if (result.link == null || result.link == '' || result.link == undefined) {

            this.setGroupLink(result.channel_id)
              .then(link => {
                this.qr_value = link
              })

          } else {
            this.qr_value = result.link
          }

        })
    },

    cancelSetup() {
      this.step = 1
    },
  }

}
</script>
