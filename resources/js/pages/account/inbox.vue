<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('messages.inbox')"></PageTitle>
    </v-row>

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
  middleware: ['auth'],
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