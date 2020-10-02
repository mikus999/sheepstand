<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('general.home')"></PageTitle>
    </v-row>

    <v-row>

      <v-toolbar flat extended>
        <v-select class="offset-md-3 col-md-6"></v-select>

        <template v-slot:extension>
          <v-tabs v-model="tab" centered align-with-title fixed-tabs show-arrows>
            <v-tab v-for="n in daysOfWeek" :key="n">{{ n }}</v-tab>
          </v-tabs>
        </template>
      </v-toolbar>

      <v-tabs-items v-model="tab">
        <v-tab-item v-for="n in daysOfWeek" :key="n">
          <v-row>
            <ShiftCard v-for="shift in shifts" :key="shift.id" :shift="shift" width="400px" height="300px" class="ma-3"></ShiftCard>
          </v-row>
        </v-tab-item>
      </v-tabs-items>

    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftCard from '~/components/ShiftCard.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    ShiftCard
  },

  data () {
    return {
      tab: null,
      shifts: null
    }
  },

  computed: {
    daysOfWeek () {
      var days = []
      days = this.$dayjs().localeData().weekdaysMin()
      days[7] = days[0]
      days.splice(0,1)
      return days
    }
  },

  created() {
    this.getShiftData(1)
  },

  methods: {

    async getShiftData (schedId) {
      await axios.get('/api/schedules/' + schedId + '/shifts')
        .then(response => {
          this.shifts = response.data
        })

    },
  }

}
</script>
