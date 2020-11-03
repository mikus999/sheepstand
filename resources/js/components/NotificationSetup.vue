<template>
<v-card class="mx-auto" max-width="600" outlined>
  <v-card-title>
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
                {{ $t('notifications.feature_explanation') }}
              </p>
              <p class="text-muted my-8">
                {{ $t('notifications.sheepstand_uses_telegram') }}
                {{ $t('notifications.if_no_telegram_account') }}
              </p>
              <p class="my-8">
                <TelegramDL />
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
                <MazPhoneNumberInput v-model="phone_num" show-code-on-list size="lg" :dark="$vuetify.theme.dark" fetch-country />
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
                <CodeInput v-model="login_code" :fields="5" @complete="codeInputComplete" class="mx-auto" />
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
                <v-text-field v-model="password" id="txtPassword" class="ma-3" label="2FA Password" outlined :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"></v-text-field>
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
                <v-text-field v-model="group_name" id="txtGroupName" class="ma-3" label="Group Name" outlined></v-text-field>
                <v-text-field v-model="group_desc" id="txtGroupDesc" class="ma-3" label="Group Description (optional)" outlined></v-text-field>
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

              <v-btn text @click="cancelSetup">{{ $t('general.close') }}</v-btn>
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
import TelegramDL from '~/components/TelegramDL'
import CodeInput from "vue-verification-code-input"
import {
  MazPhoneNumberInput
} from 'maz-ui'
import 'maz-ui/lib/scss/base.scss'
import 'maz-ui/lib/scss/maz-input.scss'
import 'maz-ui/lib/scss/maz-phone-number-input.scss'

export default {
  name: 'NotificationSetup',
  mixins: [helper, mtproto],
  components: {
    TelegramDL,
    MazPhoneNumberInput,
    CodeInput
  },

  data() {
    return {
      showPwd: false,
    }
  },

  created() {

  },

  methods: {
    cancelSetup() {
      this.signOut()
      this.stepperGo(1)
    },

    codeInputComplete(value) {
      this.login_code = value
      this.signIn()
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
</style>
