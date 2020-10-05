<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('general.home')"></PageTitle>
    </v-row>

    <v-row>
      <ShiftSchedule />
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftSchedule from '~/components/ShiftSchedule.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    ShiftSchedule
  },

  data () {
    return {

    }
  },

  created() {
  },

  methods: {
  }

}
</script>