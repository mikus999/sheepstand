<template>
  <v-alert
    width="100%"
    :color="message.color"
    :icon="message.icon"
    border="left"
    outlined
    text
    dense
    dismissible
    @input="markAsRead"
  >
    <router-link :to="{name: named_route}" class="text-decoration-none">{{ message.message_subject }}</router-link>
  </v-alert>
</template>


<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: "MessageBanner",
  mixins: [helper],
  props: {
    message: {
      type: [Array, Object]
    }
  },

  
  computed: {
    named_route() {
      return this.message.named_route ? this.message.named_route : 'account.inbox'
    },
  },

  methods: {
    async markAsRead () {
      await axios({
        method: 'get',      
        url: '/api/messages/' + this.message.id + '/markread',
      })
      .then(response => {    
        this.getMessageCounts()    
      })
    },
  },
}
</script>