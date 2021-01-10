<template>
  <v-container fluid>
    <v-row class="mb-12">
      <Schedules :key="schedules_key" v-on:updated="forceRerender" />
    </v-row>

    <v-row class="mb-12">
      <Schedules :key="templates_key" templates v-on:updated="forceRerender" />
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import Schedules from '~/components/Schedules.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'sidebar',
  mixins: [helper],

  components: {
    Schedules
  },

  data() {
    return {
      schedules_key: 1,
      templates_key: 1
    }
  },

  methods: {
    forceRerender() {
      this.schedules_key += 1
      this.templates_key += 1
    },
  },

}

</script>