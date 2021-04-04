<template>
  <v-container fluid>
    <NotificationJoin />

  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import NotificationJoin from '~/components/NotificationJoin.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'sidebar',
  mixins: [helper],
  components: {
    NotificationJoin
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