<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        Scheduling
      </h1>
    </v-row>

    <v-row>
      <v-col md="12">
        <v-data-table :headers="schedHeaders" :items="schedData" sort-by="date_start" sort-desc>
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title>Schedules</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-dialog v-model="dialog" max-width="500px">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn color="secondary" class="mb-2" v-bind="attrs" v-on="on">Create New Schedule</v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="headline">Create New Schedule</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12">
                          <v-menu ref="menu" v-model="menu" :close-on-content-click="false" :return-value.sync="date"
                            transition="scale-transition" offset-y min-width="290px">

                            <template v-slot:activator="{ on, attrs }">
                              <v-text-field v-model="newSchedDate" label="Choose a start date" prepend-icon="mdi-calendar" readonly
                                v-bind="attrs" v-on="on"></v-text-field>
                            </template>
                            <v-date-picker v-model="newSchedDate" no-title scrollable first-day-of-week="1">
                              <v-spacer></v-spacer>
                              <v-btn text color="primary" @click="menu = false">Cancel</v-btn>
                              <v-btn text color="primary" @click="$refs.menu.save(date)">OK</v-btn>
                            </v-date-picker>
                          </v-menu>

                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                    <v-btn color="blue darken-1" text @click="save">Create</v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-toolbar>
          </template>

          <template v-slot:item.status="{ item }">
            <div :class="getScheduleStatusColor(item.status)+'--text'">{{ getScheduleStatusText(item.status) }}</div>
          </template>

          <template v-slot:item.availableday_mon="{ item }">
            <v-icon small :color="getDayColor(item.availableday_mon)">{{ getDayIcon(item.availableday_mon) }}</v-icon>
          </template>

          <template v-slot:item.availableday_tues="{ item }">
            <v-icon small :color="getDayColor(item.availableday_tues)">{{ getDayIcon(item.availableday_tues) }}</v-icon>
          </template>  

          <template v-slot:item.availableday_wed="{ item }">
            <v-icon small :color="getDayColor(item.availableday_wed)">{{ getDayIcon(item.availableday_wed) }}</v-icon>
          </template>

          <template v-slot:item.availableday_thur="{ item }">
            <v-icon small :color="getDayColor(item.availableday_thur)">{{ getDayIcon(item.availableday_thur) }}</v-icon>
          </template>

          <template v-slot:item.availableday_fri="{ item }">
            <v-icon small :color="getDayColor(item.availableday_fri)">{{ getDayIcon(item.availableday_fri) }}</v-icon>
          </template>  

          <template v-slot:item.availableday_sat="{ item }">
            <v-icon small :color="getDayColor(item.availableday_sat)">{{ getDayIcon(item.availableday_sat) }}</v-icon>
          </template>

          <template v-slot:item.availableday_sun="{ item }">
            <v-icon small :color="getDayColor(item.availableday_sun)">{{ getDayIcon(item.availableday_sun) }}</v-icon>
          </template>
          
          <template v-slot:item.actions="{ item }">
            <v-icon small @click="editSched(item)" class="mr-2">
              mdi-pencil
            </v-icon>
            <v-icon small @click="deleteSched(item)" class="mr-2">
              mdi-delete
            </v-icon>
          </template>
        </v-data-table>

        <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
          {{ snackText }}

          <template v-slot:action="{ attrs }">
            <v-btn v-bind="attrs" text @click="snack = false">Close</v-btn>
          </template>
        </v-snackbar>

      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
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
        { text: 'Status', align: 'start', value: 'status' },
        { text: 'Start Date', value: 'date_start' },
        { text: 'Mon', value: 'availableday_mon', sortable: false },
        { text: 'Tues', value: 'availableday_tues', sortable: false },
        { text: 'Wed', value: 'availableday_wed', sortable: false },
        { text: 'Thur', value: 'availableday_thur', sortable: false },
        { text: 'Fri', value: 'availableday_fri', sortable: false },
        { text: 'Sat', value: 'availableday_sat', sortable: false },
        { text: 'Sun', value: 'availableday_sun', sortable: false },
        { text: 'Template Name', value: 'schedule_template_id', sortable: false },
        { text: 'Actions', value: 'actions', sortable: false },
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

  computed: {
    ...mapGetters({
      user: 'auth/user',
      team: 'teams/getTeam',
    })
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
      await axios.get('/api/schedules')
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

    deleteSched (sched) {
      const index = this.schedData.indexOf(sched.id)
      if (confirm('Are you sure you want to delete this schedule?')) {
        axios.delete('/api/schedules/' + sched.id)
          .then(response => {
            this.schedData.splice(index, 1)  
            this.snack = true
            this.snackColor = 'success'
            this.snackText = response.data.message
          })

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
        formData.append('team_id', this.formatJSON(this.team).id)
        formData.append('date_start', this.newSchedDate)
        axios.post('/api/schedules', formData)
          .then(response => {
            this.schedData.push(response.data.schedule)

            this.snack = true
            this.snackColor = 'success'
            this.snackText = response.data.message
          })
      }

      this.newSchedDate = ''
      this.close()
    }
  }
}

</script>