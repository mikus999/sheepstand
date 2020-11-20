<template>
  <v-container fluid>
    <v-row>
      <v-col cols=12>
        <MessageList :key="messageList_key"/>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import helper from '~/mixins/helper'
import MessageList from '~/components/MessageList.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
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