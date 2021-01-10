<template>
  <v-container fluid>
    <v-row>
      <MessageList show-title :flat="$vuetify.breakpoint.xs" :key="messageList_key"/>
    </v-row>
  </v-container>
</template>

<script>
import helper from '~/mixins/helper'
import MessageList from '~/components/MessageList.vue'

export default {
  middleware: ['auth'],
  layout: 'sidebar',
  mixins: [helper],
  components: {
    MessageList
  },

  
  data () {
    return {
      messageList_key: 1,
    }
  },
}
</script>