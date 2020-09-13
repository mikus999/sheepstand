<template>
  <v-container fluid>
    <v-row>
      <v-btn icon @click="$router.go(-1)" class="mr-2">
        <v-icon>mdi-arrow-left</v-icon>
      </v-btn>
      <h1 class="display-1">
        {{ $t('schedules.schedule') }}: {{ schedData.date_start }}
      </h1>
    </v-row>

    <v-row>
      <v-col md="12">
        <v-data-table :headers="shiftHeaders" :items="shiftData" sort-by="time_start" sort-asc>
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title>{{ $t('schedules.assignments') }}</v-toolbar-title>
            </v-toolbar>
          </template>

          <template v-slot:item.day="{ item }">
            {{ item.time_start | formatDay }}
          </template>

          <template v-slot:item.shift_time="{ item }">
            {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
          </template>

          <template v-slot:item.location="{ item }">
            <v-chip label small :color="item.location.color_code">{{ item.location.name }}</v-chip>
          </template>

          <template v-slot:item.participants="{ item }">
            <v-chip>
              <v-avatar left size=12 :color="checkMinMax(item.min_participants, item.users.length, 'min')">{{ item.min_participants }}</v-avatar>
              <v-avatar right size=12 :color="checkMinMax(item.max_participants, item.users.length, 'max')">{{ item.max_participants }}</v-avatar>
            </v-chip>
          </template>

          <template v-slot:item.assignments="{ item }">

            <v-select v-model="item.users" :items="teamUsers" dense
                hide-details multiple class="no-border"
                return-object item-text="name" item-value="id" :id="'shift'+item.id">

              <template v-slot:prepend-inner>
                <v-chip small label color="grey darken-3">{{ item.users.length }}</v-chip>
              </template>

              <template v-slot:selection="data">
                <v-chip small label v-bind="data.attrs" :input-value="data.selected" close
                    :color="shiftStatus[item.users[data.index].pivot.status].color"
                    @click:close="removeShiftUser(data, item)">
                  {{ data.item.name}}
                </v-chip>
              </template>

              <template v-slot:item="data">
                <v-list-item-avatar>
                  <v-icon :color="data.attrs['aria-selected']==='true' ? 'green' : 'red'">mdi-checkbox-blank-circle</v-icon>
                </v-list-item-avatar>
                <v-list-item-content @click="addShiftUser(data, item)">
                  <v-list-item-title>{{ data.item.name }}</v-list-item-title>
                  <v-list-item-subtitle></v-list-item-subtitle>
                </v-list-item-content>
              </template>

            </v-select>

          </template>
        </v-data-table>
      </v-col>
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
      shiftData: [],
      shiftHeaders: [
        { text: this.$t('shifts.day'), align: 'start', value: 'day' },
        { text: this.$t('shifts.shift_time'), align: 'start', value: 'shift_time' },
        { text: this.$t('shifts.location'), value: 'location', align: 'center'},
        { text: this.$t('shifts.participants'), value: 'participants', align: 'center', sortable: false},
        { text: this.$t('shifts.assignments'), value: 'assignments', align: 'start', sortable: false, width: '50%'},
      ],
      teamUsers: [],
      shiftUsers: {},
    }
  },

  created () {
    this.initialize()
  },

  methods: {
    initialize () {
      this.getSchedData()
      this.getShiftData()
      this.getTeamUsers()
    },

    async getSchedData () {
      await axios.get('/api/schedules/show/' + this.id)
        .then(response => {
          this.schedData = response.data
          this.date = moment(this.schedData.date_start).format("YYYY-MM-DD")

        })

    },

    async getShiftData () {
      await axios.get('/api/schedules/' + this.id + '/shifts')
        .then(response => {
          this.shiftData = response.data
          
          for (const shift of response.data) {
            this.shiftUsers[shift.id] = shift.users
          }
          
        })
    },

    async getTeamUsers (date) {
      await axios.get('/api/teams/users/' + this.team.id)
        .then(response => {
          this.teamUsers = response.data
        })

    },

    async removeShiftUser (data, shift) {
      var user = data.item
      var attrs = data.attrs

      await axios({
        method: 'post',      
        url: '/api/schedules/leaveshift',
        data: {
          user_id: user.id,
          shift_id: shift.id,
        }
      })
      .then(response => {
        shift.users = response.data.shiftusers
      })
    },

    async addShiftUser (data, shift) {
      var user = data.item
      var attrs = data.attrs


      if (attrs['aria-selected']==='true') {
        this.removeShiftUser(data, shift)

      } else {

        await axios({
          method: 'post',      
          url: '/api/schedules/joinshift',
          data: {
            user_id: user.id,
            shift_id: shift.id,
            status: this.team.setting_shift_assignment_autoaccept ? 2 : 0
          }
        })
        .then(response => {
          shift.users = response.data.shiftusers
        })
      }

    },

    checkMinMax (target, actual, minOrMax) {
        var color = 'green darken-4'
        var outOfBounds = false

        if (target !== actual) {
          if (minOrMax === 'min') {
            outOfBounds = actual < target ?  true : false
          } else if (minOrMax === 'max') {
            outOfBounds = actual > target ?  true : false
          }

          color = outOfBounds ? 'red darken-4' : 'green'
        }

        return color
      },
  }
}

</script>

<style>
  .no-border.v-text-field>.v-input__control>.v-input__slot:before {
      border-style: none;
  }
  .no-border.v-text-field>.v-input__control>.v-input__slot:after {
      border-style: none;
  }
</style>