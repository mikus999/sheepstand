<template>
  <v-card>
    <v-data-table :headers="headers" :items="shifts" disable-sort width="100%">
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>mdi-calendar-account</v-icon>
            {{ $t('shifts.my_shifts') }}
          </v-toolbar-title>
        </v-toolbar>
      </template>
    </v-data-table>
  </v-card>
</template>


<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'MyShifts',
  mixins: [helper],
  props: {},
  components: {
  },

  data () {
    return {
      shiftOverlay: false,
      shift: null,
      schedule: null,
      shifts: [],
      headers: [
        { text: '', value: 'view', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('shifts.trade_requests'), value: 'tradewith', align: 'left' }
      ],
    }
  },


}

</script>