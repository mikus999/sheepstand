<template>
  <v-container fluid>
    <v-row>
      <v-col cols=12 md=6>
        <MessageNew v-on:updated="forceRerender"/>
      </v-col>

      <v-col cols=12 md=6>
        <MessageList :messages="messages" :key="message_key" editor/>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import MessageNew from '~/components/MessageNew.vue'
import MessageList from '~/components/MessageList.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    MessageNew,
    MessageList
  },
  
  data () {
    return {
      messages: {},
      message_key: 1
    }
  },

  created () {
  },

  methods: {
    forceRerender() {
      this.message_key += 1;
    }
  }
}
</script>