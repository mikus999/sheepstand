<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('general.home')"></PageTitle>
    </v-row>

    <v-row>
    </v-row>

    <v-row>
      <v-col cols=12 lg=8>
        <MyShifts />

        <v-spacer class="my-12" />

        <TradeRequests />
      </v-col>
      
      <v-col cols=12 lg=4>
        <ShiftStatistics v-if="$vuetify.breakpoint.smAndUp"/>
      </v-col>
    </v-row>

  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftStatistics from '~/components/ShiftStatistics.vue'
import TradeRequests from '~/components/TradeRequests.vue'
import MyShifts from '~/components/MyShifts.vue'


export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    ShiftStatistics,
    TradeRequests,
    MyShifts
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
