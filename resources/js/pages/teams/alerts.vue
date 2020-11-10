<template>
  <AlertNew />
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import AlertNew from '~/components/AlertNew.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    AlertNew
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