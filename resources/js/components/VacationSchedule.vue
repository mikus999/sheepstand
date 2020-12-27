<template>
  <v-card width="100%" :flat="$vuetify.breakpoint.xs">
    <v-card-title class="text-h6">
      <v-icon class="mr-3">mdi-palm-tree</v-icon>
      {{ $t('account.vacation_schedule') }}
    </v-card-title>

    <!-- Desktop View -->
    <v-card-text>
      <v-row>
        <v-col cols=12 sm=6 md=12>
          <v-date-picker
            v-model="vacation_dates"
            width="100%"
            range
            :locale="locale"
            no-title
            :show-current="false"
          ></v-date-picker>

          <v-textarea
            v-model="vacation_note"
            auto-grow
            outlined
            rows=2
            :label="$t('general.note') + ' (' + $t('general.optional') + ')'"
          >
          </v-textarea>

          <v-btn
            color="primary"
            block
            @click="saveVacation"
          >
            {{ $t('general.save') }}
          </v-btn>
        </v-col>

        <v-col cols=12 sm=6 md=12>
          <v-data-table
            :headers="headers"
            :items="this.user.user_vacations"
            sort-by="date_start"
          >

            <template v-slot:item.date_start="{ item }">
              {{ $dayjs(item.date_start).format('l') }}
            </template>

            <template v-slot:item.date_end="{ item }">
              {{ $dayjs(item.date_end).format('l') }}
            </template>

            <template v-slot:item.actions="{ item }">
              <v-btn 
                icon 
                small
                @click="deleteVacation(item.id)"
              >
                <v-icon small>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
        </v-col>
      </v-row>


    </v-card-text>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: "VacationSchedule",
  mixins: [helper],

  props: {
    data: {
      type: [Object, Array]
    }
  },

  data() {
    return {
      vacation_dates: [],
      vacation_note: null,
      headers: [
        {
          text: this.$t('schedules.start_date'),
          align: 'start',
          value: 'date_start',
          width: '100px',
          sortable: false
        },
        {
          text: this.$t('schedules.end_date'),
          align: 'start',
          value: 'date_end',
          width: '100px',
          sortable: false
        },
        {
          text: this.$t('general.note'),
          align: 'start',
          value: 'note',
          sortable: false
        },
        {
          text: null,
          align: 'end',
          value: 'actions',
          sortable: false
        },
      ],
    }
  },

  methods: {

    async saveVacation () {
      if (this.vacation_dates.length > 0) {
        var date_start = this.vacation_dates[0]
        var date_end = this.vacation_dates[this.vacation_dates.length - 1]
        const note = this.vacation_note


        // Make sure start date is before end date
        if (this.$dayjs(date_start).isAfter(this.$dayjs(date_end))) {
          var temp_date = date_start
          date_start = date_end
          date_end = temp_date
        }


        await axios({
          method: 'post',      
          url: '/api/account/vacation',
          data: {
            date_start: date_start,
            date_end: date_end,
            note: note
          }
        })
        .then(response => {
          this.vacation_dates = []
          this.vacation_note = null
          this.showSnackbar(this.$t('general.info_updated'), 'success')
          this.refreshStore()
        })
      }
    },

    async deleteVacation(id) {
      if (await this.$root.$confirm(this.$t('account.confirm_delete_vacation'), null, 'error')) {
        await axios({
          method: 'delete',      
          url: '/api/account/vacation/' + id
        })
        .then(response => {
          this.showSnackbar(this.$t('general.info_updated'), 'success')
          this.refreshStore()
        })
      }
    },


  },
}
</script>