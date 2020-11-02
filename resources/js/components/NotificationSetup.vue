<template>
  <v-card class="mx-auto" max-width="600" outlined>
    <v-card-title>
      Notifications Setup
    </v-card-title>

    <v-card-text>
      <v-stepper v-model="stepper">
        <v-stepper-items>
          <v-stepper-content step="1">
            <v-row>
              <v-col cols=12 class="my-5 text-center">
                <h3 class="ma-3">Let's setup notifications for your team</h3>
                <p class="text-muted my-8">
                  Enabling this feature ensures your team is notified about trade offers, new schedules, etc.
                </p>
                <p class="text-muted my-8">
                  SheepStand uses Telegram for notifications.
                  Telegram is a free and secure messaging app, similar to Viber and WhatsApp.
                  If you do not already have a Telegram account, you can download the app from the link below and sign up.
                </p>
                <p class="my-8">
                  <TelegramDL />
                </p>
                <p class="text-muted my-8">
                  After signing up for an account, or if you are already a Telegram user, continue to the next step.
                </p>
                        
                <v-btn color="primary" @click="stepperGo(2)">I have a Telegram account</v-btn>
              </v-col>
            </v-row>
          </v-stepper-content>



          <v-stepper-content step="2">
            <v-row>
              <v-col cols=12 class="my-5 text-center">
                <h3 class="ma-3">Sign In to Telegram</h3>
                <p class="text-muted my-8">
                  Please enter the phone number linked to your Telegram account. 
                  A code will be sent to this number in order to sign in.
                </p>

                <div class="my-16">
                  <MazPhoneNumberInput v-model="phone_num" show-code-on-list size="lg" :dark="$vuetify.theme.dark" fetch-country/>
                </div>

                <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                  {{ $t('general.error') + ': ' + error_msg }}
                </p>

                <v-btn text @click="cancelSetup">Cancel</v-btn>
                <v-btn color="primary" @click="sendCode()">Continue</v-btn>
              </v-col>
            </v-row>
          </v-stepper-content>



          <v-stepper-content step="3">
            <v-row>
              <v-col cols=12 class="my-5 text-center">
                <h3 class="ma-3">Enter the Login Code</h3>
                <p class="text-muted my-8">
                  Open Telegram on your device and find the login code you were sent. Enter the code below.
                </p>

                <div class="my-16 text-center">
                  <CodeInput v-model="login_code" :fields="5" @complete="codeInputComplete" class="mx-auto" />
                </div>

                <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                  {{ $t('general.error') + ': ' + error_msg }}
                </p>

                <v-btn text @click="cancelSetup">Cancel</v-btn>
                <v-btn color="primary" @click="signIn()">Continue</v-btn>
              </v-col>
            </v-row>
          </v-stepper-content>



          <v-stepper-content step="4">
            <v-row>
              <v-col cols=12 class="my-5 text-center">
                <h3 class="ma-3">Enter your Telegram Password</h3>
                <p class="text-muted my-8">
                  It looks like you enabled 2-Factor Authentication on your account. Please enter you 2FA password to sign in.
                </p>

                <div class="my-16 text-center">
                  <v-text-field v-model="password" id="txtPassword" class="ma-3" label="2FA Password" outlined
                    :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"></v-text-field>
                </div>

                <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                  {{ $t('general.error') + ': ' + error_msg }}
                </p>

                <v-btn text @click="cancelSetup">Cancel</v-btn>
                <v-btn color="primary" @click="check2FA(password)">Continue</v-btn>
              </v-col>
            </v-row>
          </v-stepper-content>



          <v-stepper-content step="5">
            <v-row>
              <v-col cols=12 class="my-5 text-center">
                <h3 class="ma-3">Great! You're signed in to Telegram</h3>
                <p class="text-muted my-8">
                  Now let's create a Telegram group for your team. 
                  Team members can then join the group in order to receive notifications.
                </p>

                <div class="my-16 text-center">
                  <v-text-field v-model="group_name" id="txtGroupName" class="ma-3" label="Group Name" outlined></v-text-field>
                  <v-text-field v-model="group_desc" id="txtGroupDesc" class="ma-3" label="Group Description (optional)" outlined></v-text-field>
                </div>

                <p class="text-muted mb-8 mt-n12 red--text" v-if="error_msg">
                  {{ $t('general.error') + ': ' + error_msg }}
                </p>

                <v-btn text @click="cancelSetup">Cancel</v-btn>
                <v-btn color="primary" @click="createSuperGroup()">Continue</v-btn>
              </v-col>
            </v-row>
          </v-stepper-content>



          <v-stepper-content step="6">
            <v-row>
              <v-col cols=12 class="my-5 text-center">
                <h3 class="ma-3">Setup Complete!</h3>
                <p class="text-muted text-body-2 my-8">
                  Congratulations! Notifications have been enabled on your account. 
                  You can now access notification settings on the Team Settings page.
                </p>

                <v-btn text @click="cancelSetup">Close</v-btn>
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
import { MazPhoneNumberInput } from 'maz-ui'
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

  data () {
    return {
      showPwd: false,
    }
  },

  created () {

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

.react-code-input > input {
  color: #0288D1 !important;
  font-weight: bold;
  font-size: 24pt !important;
}

</style>