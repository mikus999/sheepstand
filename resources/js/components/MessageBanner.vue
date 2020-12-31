<template>
  <v-alert
    width="100%"
    :color="message.color"
    :type="message.type || 'info'"
    :icon="message.icon"
    :outlined="message.outlined == 1"
    dismissible
    @input="markAsRead"
  >
    {{ message.system_message ? $t(message.message_i18n_string) : message.message_text }}
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