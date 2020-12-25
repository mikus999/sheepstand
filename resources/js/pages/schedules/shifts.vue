<template>
  <v-container fluid>
    <v-row v-if="$vuetify.breakpoint.smAndUp">
      <PageTitle :title="$t('schedules.available_shifts')"></PageTitle>
    </v-row>

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
  layout: 'vuetify',
  mixins: [helper],
  components: {
    ShiftSchedule,
    ShiftScheduleMobile,
  },
}
</script>