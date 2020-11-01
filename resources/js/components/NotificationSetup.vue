<template>
  <v-card class="mx-auto" max-width="600" outlined>
    <v-stepper v-model="stepper" vertical>
      <v-stepper-step :complete="stepper > 1" step="1">
        Notifications Setup
      </v-stepper-step>
      <v-stepper-content step="1">
        <v-row>
          <v-col cols=12 class="mt-5 text-center">
            <h3 class="ma-3">Let's setup notifications for your team</h3>
            <blockquote class="blockquote text-muted">
              Notifications explanations<br>
              Why we use Telegram<br>
              Must have a Telegram account to continue
            </blockquote>
                    
            <v-btn color="primary" @click="stepperGo(2)">Continue</v-btn>
            <v-btn text @click="cancelSetup">Cancel</v-btn>
          </v-col>
        </v-row>
      </v-stepper-content>



      <v-stepper-step :complete="stepper > 2" step="2">
        Sign In to Telegram
      </v-stepper-step>
      <v-stepper-content step="2">
        <v-row>
          <v-col cols=12 class="mt-5 text-center">
            <h3 class="ma-3">Sign In to Telegram</h3>
            <blockquote class="blockquote text-muted">
              Explain phone number format (+xxxyyyyyyyy)
            </blockquote>

            <v-text-field v-model="phone_num" id="txtPhone" class="ma-3" label="Phone Number" hint="+xxxxxxxxxxx" outlined></v-text-field>

            <v-btn color="primary" @click="sendCode()">Continue</v-btn>
            <v-btn text @click="cancelSetup">Cancel</v-btn>
          </v-col>
        </v-row>
      </v-stepper-content>



      <v-stepper-step :complete="stepper > 3" step="3">
        Enter Login Code
      </v-stepper-step>
      <v-stepper-content step="3">
        <v-row>
          <v-col cols=12 class="mt-5 text-center">
            <h3 class="ma-3">Enter the Login Code</h3>
            <blockquote class="blockquote text-muted">
              Open Telegram on your device and find the login code you were sent. Enter the code below.
            </blockquote>

            <v-text-field v-model="login_code" id="txtCode" class="ma-3" label="Login Code" outlined></v-text-field>

            <v-btn color="primary" @click="signIn()">Continue</v-btn>
            <v-btn text @click="cancelSetup">Cancel</v-btn>
          </v-col>
        </v-row>
      </v-stepper-content>



      <v-stepper-step :complete="stepper > 4" step="4">
        Check Password (2FA)
      </v-stepper-step>
      <v-stepper-content step="4">
        <v-row>
          <v-col cols=12 class="mt-5 text-center">
            <h3 class="ma-3">Enter your Telegram Password</h3>
            <blockquote class="blockquote text-muted">
              It looks like you enabled 2-Factor Authentication on your account. Please enter you 2FA password to sign in.
            </blockquote>

            <v-text-field v-model="password" id="txtPassword" class="ma-3" label="2FA Password" outlined
              :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"></v-text-field>

            <v-btn color="primary" @click="check2FA(password)">Continue</v-btn>
            <v-btn text @click="cancelSetup">Cancel</v-btn>
          </v-col>
        </v-row>
      </v-stepper-content>



      <v-stepper-step :complete="stepper > 5" step="5">
        Create Telegram Group
      </v-stepper-step>
      <v-stepper-content step="5">
        <v-row>
          <v-col cols=12 class="mt-5 text-center">
            <h3 class="ma-3">Great! You're signed in to Telegram</h3>
            <blockquote class="blockquote text-muted">
              Now let's create a Telegram group for your team. 
              Team members can then join the group in order to receive notifications.
            </blockquote>

            <v-text-field v-model="group_name" id="txtGroupName" class="ma-3" label="Group Name" outlined></v-text-field>
            <v-text-field v-model="group_desc" id="txtGroupDesc" class="ma-3" label="Group Desc" outlined></v-text-field>

            <v-btn color="primary" @click="createSuperGroup()">Continue</v-btn>
            <v-btn text @click="cancelSetup">Cancel</v-btn>
          </v-col>
        </v-row>
      </v-stepper-content>



      <v-stepper-step step="6">
        Setup Complete
      </v-stepper-step>
      <v-stepper-content step="6">
        <v-row>
          <v-col cols=12 class="mt-5 text-center">
            <h3 class="ma-3">Setup Complete!</h3>
            <blockquote class="blockquote text-muted">
              Congratulations! Notifications have been enabled on your account. 
              You can now access notification settings on the Team Settings page.
            </blockquote>

            <v-btn text @click="cancelSetup">Close</v-btn>
          </v-col>
        </v-row>


        <v-card class="mb-12" height="200px">
          Congratulations! Notifications have been enabled on your account. 
          You can now access notification settings on the Team Settings page.
        </v-card>
        <v-btn text @click="cancelSetup">Close</v-btn>
      </v-stepper-content>
    </v-stepper>



    <p>
      <v-btn @click="signOut">Sign Out</v-btn>
      <v-btn @click="deleteSuperGroup">Delete Group</v-btn>
    </p>
  </v-card>
  
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import mtproto from '~/mixins/telegram'
import { MTProto } from '@mtproto/core'

export default {
  name: 'NotificationSetup',
  mixins: [helper, mtproto],
  data () {
    return {
      showPwd: false,
    }
  },

  methods: {
    cancelSetup() {
      this.stepperGo(1)
    }
  }
}
</script>