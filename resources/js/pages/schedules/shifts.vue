<template>
  <v-container fluid>
    <v-row>
      <ShiftSchedule v-if="$vuetify.breakpoint.smAndUp" />
      <ShiftScheduleMobile v-else />
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftSchedule from '~/components/ShiftSchedule.vue'
import ShiftScheduleMobile from '~/components/ShiftScheduleMobile.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'sidebar',
  mixins: [helper],
  components: {
    ShiftSchedule,
    ShiftScheduleMobile,
  },
}
</script>