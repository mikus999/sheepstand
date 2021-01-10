<template>
  <v-container fluid>
    <v-row>
      <v-col cols=12 md=6>
        <MessageNew v-on:updated="forceRerender" v-on:clear="clearMessageNew" :key="messageNew_key"/>
      </v-col>

      <v-col cols=12 md=6>
        <MessageList :key="messageList_key" editor show-title />
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
  layout: 'sidebar',
  mixins: [helper],
  components: {
    MessageNew,
    MessageList
  },
  
  data () {
    return {
      messageNew_key: 1,
      messageList_key: 1,
    }
  },

  created () {
  },

  methods: {
    forceRerender() {
      this.messageNew_key += 1
      this.messageList_key += 1
    },

    clearMessageNew() {
      this.messageNew_key += 1
    }
  }
}
</script>