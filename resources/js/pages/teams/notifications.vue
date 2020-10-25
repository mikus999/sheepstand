<template>
  <v-container fluid>
    <p>
      <v-text-field v-model="phoneNum" id="txtPhone"></v-text-field>
      <v-btn @click="sendCode">Send Code</v-btn>
    </p>
    <p>
      <v-text-field v-model="code" id="txtCode"></v-text-field>
      <v-btn @click="signIn">Sign In</v-btn>
    </p>

    <p>
      <v-btn @click="getUserInfo">Get User Info</v-btn>
    </p>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import mtproto from '~/plugins/telegram'
import { MTProto } from '@mtproto/core'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, mtproto],
  components: {
  },
  
  data () {
    return {
      telegram: null,
      api_id: '1963687',
      api_hash: '756735263dc2835ebfd1e02dfdf5dcbd',
      phoneNum: '+381638155702',
      code: null
    }
  },

  created () {
    this.initialize()
  },

  methods: {
    initialize () {
    },

  }

}

</script>