<template>
  <v-container fluid>
    <v-row>


    </v-row>
  </v-container>
</template>

<script>
import helper from '../mixins/helper'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  created () {
    console.log(this.hasPermission('test'))
    console.log(this.hasPermission('view_shifts'))

  },

}
</script>
