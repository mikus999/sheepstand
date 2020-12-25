<template>
  <v-container fluid>
    <v-row v-if="$vuetify.breakpoint.smAndUp">
      <PageTitle :title="$t('messages.inbox')"></PageTitle>
    </v-row>

    <v-row>
      <MessageList :show-title="$vuetify.breakpoint.xs" :key="messageList_key"/>
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