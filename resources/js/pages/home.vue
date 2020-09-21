<template>
  <v-container fluid>
    <v-row>
      <h4>Home</h4>

    </v-row>
  </v-container>
</template>

<script>
import helper from '~/mixins/helper'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  mounted () {
  },

}
</script>
