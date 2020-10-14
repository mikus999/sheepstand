<template>
  <v-container fluid>

    <LocationMap />
    
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import LocationMap from '~/components/LocationMap.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    LocationMap
  }

}
</script>