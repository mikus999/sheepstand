<template>
  <v-container fluid>

    <LocationMap v-if="location" :location="location" :fill="location.color_code"  />
    
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
  },
  
  data () {
    return {
      location: null
    }
  },

  created () {
    this.getData()
  },

  methods: {
    async getData () {
      await axios.get('/api/teams/21/locations/1')
        .then(response => {
          this.location = response.data
        })
    },
  }
}
</script>