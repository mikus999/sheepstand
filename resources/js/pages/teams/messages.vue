<template>
  <v-container fluid>
    <v-row>
      <v-col cols=12 md=6>
        <MessageNew />
      </v-col>

      <v-col cols=12 md=6>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import MessageNew from '~/components/MessageNew.vue'

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