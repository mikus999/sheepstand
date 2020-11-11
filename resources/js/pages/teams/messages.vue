<template>
  <MessageNew />
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import AlertNew from '~/components/MessageNew.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    MessageNew
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