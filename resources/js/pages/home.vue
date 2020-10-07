<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('general.home')"></PageTitle>
    </v-row>

    <v-row>
      <ShiftStatistics />
      <TradeRequests />
      
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftStatistics from '~/components/ShiftStatistics.vue'
import TradeRequests from '~/components/TradeRequests.vue'


export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    ShiftStatistics,
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
