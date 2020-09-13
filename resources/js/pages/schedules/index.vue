<template>
  <v-container fluid>
    <v-row>
      <h1 class="display-1">
        {{ $t('schedules.shift_schedules') }}
      </h1>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-data-table :headers="schedHeaders" :items="schedData" sort-by="date_start" sort-desc>
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title v-show="$vuetify.breakpoint.smAndUp">{{ $t('schedules.shift_schedules') }}</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-dialog v-model="dialog" max-width="500px">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn color="secondary" class="mb-2" v-bind="attrs" v-on="on" :block="$vuetify.breakpoint.xs">{{ $t('schedules.create_new_schedule') }}</v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="headline">{{ $t('schedules.create_new_schedule') }}</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12">
                          <v-menu ref="menu" v-model="menu" :close-on-content-click="false" :return-value.sync="date"
                            transition="scale-transition" offset-y min-width="290px">

                            <template v-slot:activator="{ on, attrs }">
                              <v-text-field v-model="newSchedDate" :label="$t('schedules.choose_start_date')" prepend-icon="mdi-calendar" readonly
                                v-bind="attrs" v-on="on"></v-text-field>
                            </template>
                            <v-date-picker v-model="newSchedDate" no-title scrollable first-day-of-week="1">
                              <v-spacer></v-spacer>
                              <v-btn text color="primary" @click="menu = false">{{ $t('general.cancel') }}</v-btn>
                              <v-btn text color="primary" @click="$refs.menu.save(date)">{{ $t('general.ok') }}</v-btn>
                            </v-date-picker>
                          </v-menu>

                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="close">{{ $t('general.cancel') }}</v-btn>
                    <v-btn color="blue darken-1" text @click="save">{{ $t('general.create') }}</v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-toolbar>
          </template>

          <template v-slot:item.status="{ item }">
            <div :class="scheduleStatus[item.status].color+'--text'">{{ scheduleStatus[item.status].text }}</div>
          </template>

          <template v-slot:item.shifts_count="{ item }">
            {{ item.shifts_count }}
          </template>
          
          <template v-slot:item.actions="{ item }">
            <v-icon small @click="editSched(item)" class="mr-2">
              mdi-pencil
            </v-icon>
            <v-icon small @click="editAssignments(item)" class="mr-2" :disabled="item.status < 1">
              mdi-account-multiple-plus
            </v-icon>
            <v-icon small @click="deleteSched(item)" class="mr-2">
              mdi-delete
            </v-icon>
          </template>
        </v-data-table>

        <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
          {{ snackText }}

          <template v-slot:action="{ attrs }">
            <v-btn v-bind="attrs" text @click="snack = false">{{ $t('general.close') }}</v-btn>
          </template>
        </v-snackbar>

      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import Form from 'vform'
import helper from '../../mixins/helper'
import moment from 'moment'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  data () {
    return {
      dialog: false,
      schedHeaders: [
        { text: this.$t('schedules.status'), align: 'start', value: 'status' },
        { text: this.$t('schedules.start_date'), value: 'date_start' },
        { text: this.$t('schedules.shifts'), value: 'shifts_count', align: 'center', sortable: false },
        { text: this.$t('schedules.template_name'), value: 'schedule_template_id', sortable: false },
        { text: this.$t('general.actions'), value: 'actions', sortable: false },
      ],
      schedData: [],
      newSchedDate: '',
      date: new Date().toISOString().substr(0, 10),
      menu: false,
      snack: false,
      snackText: '',
      snackColor: ''
    }
  },

  created () {
    this.getSchedData()
  },

  methods: {
    getDayIcon(status) {
      return (status ? 'mdi-check-bold' : 'mdi-close-thick')
    },

    getDayColor(status) {
      return (status ? 'green' : 'red')
    },

    async getSchedData () {
      await axios.get('/api/schedules/' + this.team.id)
        .then(response => {
          this.schedData = response.data
        })
    },

    editSched (schedule) {
      this.$router.push({
          name: 'schedules.edit',
          params: {
              id: schedule.id
          }
      }) 
    },

    editAssignments (schedule) {
      this.$router.push({
          name: 'schedules.assignments',
          params: {
              id: schedule.id
          }
      }) 
    },

    deleteSched (sched) {
      const index = this.schedData.indexOf(sched.id)
      if (confirm(this.$t('schedules.confirm_delete_schedule'))) {
        axios.delete('/api/schedules/' + sched.id)
          .then(response => {
            this.snack = true
            this.snackColor = 'success'
            this.snackText = this.$t('schedules.success_delete_schedule')
          })

        this.getSchedData()
      }
    },

    close () {
      this.dialog = false
    },

    save () {
      if (this.newSchedDate !== '') {
        this.newSchedDate = moment(this.newSchedDate).startOf('isoWeek').format("YYYY-MM-DD")

        const formData = new FormData()
        formData.append('user_id', this.user.id)
        formData.append('team_id', this.team.id)
        formData.append('date_start', this.newSchedDate)
        axios.post('/api/schedules', formData)
          .then(response => {
            this.snack = true
            this.snackColor = 'success'
            this.snackText = this.$t('schedules.success_create_schedule')
          })
      }

      this.getSchedData()
      this.newSchedDate = ''
      this.close()
    }
  }
}

</script>