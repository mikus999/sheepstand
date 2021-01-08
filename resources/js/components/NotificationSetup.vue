<template>
<v-card class="mx-auto" max-width="600" outlined>
  <v-card-title>
    <v-icon left>{{ icons.mdiTelegram }}</v-icon>
    {{ $t('notifications.notification_setup') }}
  </v-card-title>

  <v-card-text>
    <v-stepper v-model="stepper">
      <v-stepper-items>
        <v-stepper-content step="1">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">{{ $t('notifications.lets_setup_notifications') }}</h3>
              <p class="text-muted my-8">
                {{ $t('notifications.feature_explanation_team') }}
              </p>
              <p class="text-muted my-8">
                {{ $t('notifications.sheepstand_uses_telegram') }}
                {{ $t('notifications.if_no_telegram_account') }}
              </p>
              <p class="my-8">
                <DownloadTelegram />
              </p>
              <p class="text-muted my-8">
                {{ $t('notifications.after_telegram_signup') }}
              </p>

              <v-btn color="primary" @click="stepperGo(2)">{{ $t('notifications.i_have_telegram') }}</v-btn>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="2">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">{{ $t('notifications.signin_to_telegram') }}</h3>
              <p class="text-muted my-8">
                {{ $t('notifications.enter_telegram_phone_number') }}
              </p>

              <div class="my-16">
                  <VueTelInput 
                    v-model="phone_num"
                    enabled-country-code
                    valid-characters-only
                    dynamic-placeholder
                    mode="international"
                    wrapper-classes="tel-dropdown-dark" 
                    :input-classes="$vuetify.theme.dark ? 'tel-input-dark' : ''" />
              </div>

              <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                {{ $t('general.error') + ': ' + error_msg }}
              </p>

              <v-btn text @click="cancelSetup">{{ $t('general.cancel') }}</v-btn>
              <v-btn color="primary" @click="sendCode()">{{ $t('general.continue') }}</v-btn>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="3">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">{{ $t('notifications.enter_login_code') }}</h3>
              <p class="text-muted my-8">
                {{ $t('notifications.get_telegram_login_code') }}
              </p>

              <div class="my-16 text-center">
                <CodeInput 
                  v-model="login_code" 
                  :fields="5" 
                  @complete="codeInputComplete" 
                  :fieldWidth="$vuetify.breakpoint.xsOnly ? 40 : 58" 
                  :fieldHeight="$vuetify.breakpoint.xsOnly ? 38 : 54" 
                  class="mx-auto" />

                  <p class="my-4">
                    <a @click="resendCode" class="primary--text"><v-icon small class="mr-1 primary--text">{{ icons.mdiRefresh }}</v-icon>{{$t('notifications.resend_login_code')}}</a>
                  </p>
              </div>

              <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                {{ $t('general.error') + ': ' + error_msg }}
              </p>

              <v-btn text @click="cancelSetup">{{ $t('general.cancel') }}</v-btn>
              <v-btn color="primary" @click="signIn()">{{ $t('general.continue') }}</v-btn>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="4">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">{{ $t('notifications.enter_telegram_password') }}</h3>
              <p class="text-muted my-8">
                {{ $t('notifications.2FA_explanation') }}
              </p>

              <div class="my-16 text-center">
                <v-text-field v-model="password" id="txtPassword" class="ma-3" :label="$t('notifications.2fa_password')" outlined :append-icon="showPwd ? icons.mdiEye : icons.mdiEyeOff" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"></v-text-field>
              </div>

              <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                {{ $t('general.error') + ': ' + error_msg }}
              </p>

              <v-btn text @click="cancelSetup">{{ $t('general.cancel') }}</v-btn>
              <v-btn color="primary" @click="check2FA(password)">{{ $t('general.continue') }}</v-btn>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="5">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">{{ $t('notifications.telegram_signedin') }}</h3>
              <p class="text-muted my-8">
                {{ $t('notifications.create_telegram_group') }}
              </p>

              <div class="my-16 text-center">
                <v-text-field v-model="group_name" id="txtGroupName" class="ma-3" :label="$t('notifications.group_name')" outlined></v-text-field>
                <v-text-field v-model="group_desc" id="txtGroupDesc" class="ma-3" :label="$t('notifications.group_description')" outlined></v-text-field>
              </div>

              <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                {{ $t('general.error') + ': ' + error_msg }}
              </p>

              <v-btn text @click="cancelSetup">{{ $t('general.cancel') }}</v-btn>
              <v-btn color="primary" @click="createSuperGroup()">{{ $t('general.continue') }}</v-btn>
            </v-col>
          </v-row>
        </v-stepper-content>

        <v-stepper-content step="6">
          <v-row>
            <v-col cols=12 class="my-5 text-center">
              <h3 class="ma-3">{{ $t('notifications.setup_complete') }}</h3>
              <p class="text-muted my-8">
                {{ $t('notifications.notifications_now_enabled') }}
              </p>

              <v-btn text @click="close()">{{ $t('general.close') }}</v-btn>
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
import CodeInput from "vue-verification-code-input"
import { VueTelInput } from 'vue-tel-input'

export default {
  name: 'NotificationSetup',
  mixins: [helper, mtproto],
  components: {
    DownloadTelegram,
    VueTelInput,
    CodeInput
  },

  data() {
    return {
      showPwd: false,
      codeWidth: this.$vuetify.breakpoint.xsOnly ? 40 : 58,
      codeHeight: this.$vuetify.breakpoint.xsOnly ? 38 : 54,
    }
  },

  created() {
    if (this.notificationsEnabled) {
      this.$router.push({ name: 'teams.index', params: { tab: 'general' } })
    }

    this.mtInitialize()
    this.group_name = this.team.display_name
    this.group_desc = 'SheepStand.com notifications'
  },

  methods: {
    cancelSetup() {
      this.signOut()
      this.stepperGo(1)
    },

    codeInputComplete(value) {
      this.login_code = value
      this.signIn()
    },

    close() {
      this.$router.go(-1)
    }
  }
}
</script>

<style>
.react-code-input>input {
  color: #0288D1 !important;
  font-weight: bold;
  font-size: 24pt !important;
}

.tel-input-dark {
  color: #fcfcfc;
}

.tel-dropdown-dark {
  color: #000000;
}
</style>
