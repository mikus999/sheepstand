<template>
  <v-container fluid>
    <v-row>
      <h4>Home</h4>

      <LocationMap fill="#ff0000"></LocationMap>
    </v-row>
  </v-container>
</template>

<script>
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
