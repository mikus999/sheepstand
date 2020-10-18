<template>
  <v-container fluid>

    <Leaflet v-if="location" :location="location" :fill="location.color_code" width="100%" height="800px"/>
    
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import Leaflet from '~/components/Leaflet.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    Leaflet
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