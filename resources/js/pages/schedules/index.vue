<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('schedules.shift_schedules')"></PageTitle>
    </v-row>

    <v-row class="mb-12">
      <Schedules />
    </v-row>

    <v-row class="mb-12">
      <Schedules templates />
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import Form from 'vform'
import helper from '~/mixins/helper'
import Schedules from '~/components/Schedules.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],

  components: {
    Schedules
  },

}

</script>