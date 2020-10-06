<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('general.home')"></PageTitle>
    </v-row>

    <v-row>
      <TradeRequests />
      
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import TradeRequests from '~/components/TradeRequests.vue'


export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    TradeRequests
  },

  data () {
    return {

    }
  },

  computed: {
  },

  created() {
  },

  methods: {
  }

}
</script>
