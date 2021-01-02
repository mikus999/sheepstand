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
    {{ message.message_subject }}
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