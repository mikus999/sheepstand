<template>
  <v-container>
    <v-row>
      <v-icon class="display-1 pb-2 pr-2" @click="$router.go(-1)">mdi-arrow-left</v-icon>
      <h1 class="display-1">
        {{ $t('schedules.schedule') }}: {{ schedData.date_start }}
      </h1>
    </v-row>


    <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
      {{ snackText }}

      <template v-slot:action="{ attrs }">
        <v-btn v-bind="attrs" text @click="snack = false">{{ $t('general.close') }}</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import moment from 'moment'
import helper from '../../mixins/helper'


export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],
  props: {
    id: {
      type: [String, Number],
      required: true,
    }
  },
  components: {
  },

  data () {
    return {
      dialog: false,
      date: '',
      snack: false,
      snackText: '',
      snackColor: '',
      schedData: [],
    }
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
      team: 'teams/getTeam',
    })
    
  },

  created () {
    this.initialize()
  },

  methods: {
    initialize () {
      this.getSchedData()
    },

    async getSchedData () {
      await axios.get('/api/schedules/show/' + this.id)
        .then(response => {
          this.schedData = response.data
          this.date = moment(this.schedData.date_start).format("YYYY-MM-DD")

        })

    },
  }
}

</script>