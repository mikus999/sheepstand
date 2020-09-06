<template>
  <v-container>
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
            <v-icon x-small>mdi-arrow-collapse-down</v-icon>{{ item.min_participants }}
            <v-icon x-small class="ml-2">mdi-arrow-collapse-up</v-icon>{{ item.max_participants }}
          </template>

          <template v-slot:item.assignments="{ item }">

            <v-autocomplete v-model="shiftUsers[item.id]" :items="teamUsers" dense outlined no-filter
                auto-select-first hide-details multiple hide-selected
                return-object item-text="name" item-value="id" :id="'shift'+item.id">

              <template v-slot:selection="data">
                <v-chip label small v-bind="data.attrs" :input-value="data.selected" close @click:close="removeShiftUser(data.item, item)">
                  {{ data.item.name }}
                </v-chip>
              </template>

              <template v-slot:item="data">
                <v-list-item-content @click="addShiftUser(data.item, item)">
                  <v-list-item-title v-html="data.item.name"></v-list-item-title>
                  <v-list-item-subtitle></v-list-item-subtitle>
                </v-list-item-content>
              </template>

            </v-autocomplete>

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
            this.getShiftUsers(shift.id)
          }
        })
    },

    async getShiftUsers (shift_id) {
      await axios.get('/api/schedules/shiftusers/' + shift_id)
        .then(response => {
          this.shiftUsers[shift_id] = response.data
          /*
          this.shiftUsers[shift_id] = []
          for (const user of response.data) {
            this.shiftUsers[shift_id].push(user.id)
          }
          */
        })
    },

    async getTeamUsers (date) {
      await axios.get('/api/teams/users/' + this.formatJSON(this.team).id)
        .then(response => {
          this.teamUsers = response.data
        })

    },

    async removeShiftUser (user, shift) {
      await axios({
        method: 'post',      
        url: '/api/schedules/leaveshift',
        data: {
          user_id: user.id,
          shift_id: shift.id,
        }
      })   
    },

    async addShiftUser (user, shift) {
      await axios({
        method: 'post',      
        url: '/api/schedules/joinshift',
        data: {
          user_id: user.id,
          shift_id: shift.id,
          status: 0
        }
      })    
    },
  }
}

</script>