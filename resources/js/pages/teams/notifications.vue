<template>
  <v-container fluid>
    <NotificationSetup />

  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import NotificationSetup from '~/components/NotificationSetup.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'sidebar',
  mixins: [helper],
  components: {
    NotificationSetup
  },
  
  data () {
    return {
    }
  },

  created () {
  },

  methods: {
  }

}

</script>