<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        Scheduling
      </h1>
    </v-row>

    <v-row>
      <v-col md="12">
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import Form from 'vform'

export default {
  middleware: 'auth',
  layout: 'vuetify',

  data () {
    return {
    
    }
  }
}

</script>