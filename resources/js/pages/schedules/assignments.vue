<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('schedules.schedule')"></PageTitle>
    </v-row>

    <v-card width="100%">
      <v-row>
        <v-col xs=1 sm=4 class="text-left" >
          <v-btn text :x-large="$vuetify.breakpoint.smAndUp" @click="$router.go(-1)">
            <v-icon left>mdi-arrow-left</v-icon>
            <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('general.go_back')}}</span>
          </v-btn>
        </v-col>

        <v-col xs=10 sm=4 class="text-center">
          <span class="text-h6">{{ $t('schedules.week_of')}} {{ schedData.date_start | formatDate }}</span>
        </v-col>
        
        <v-col xs=1 sm=4 class="text-right">
          <v-btn text :x-large="$vuetify.breakpoint.smAndUp" @click="">
            <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('schedules.approvals') }}</span>
            <v-icon right>mdi-arrow-right</v-icon>
          </v-btn>
        </v-col>
      </v-row>

      <v-row>
        <v-col md="12">
          <v-data-table :headers="shiftHeaders" :items="shiftData" :key="shiftTable_key" sort-by="time_start" sort-asc>
            <template v-slot:top>
              <v-toolbar flat>
                <v-toolbar-title>{{ $t('schedules.assignments') }}</v-toolbar-title>
                <v-spacer />
                <v-btn
                  color="secondary"
                  @click="approveAllRequests()"
                >
                  <v-icon small left>mdi-thumb-up</v-icon>
                  {{ $t('schedules.approve_all') }}
                </v-btn>
              </v-toolbar>
            </template>

            <template v-slot:item.day="{ item }">
              {{ item.time_start | formatDay }}<br />
              {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
            </template>

            <template v-slot:item.location="{ item }">
              <v-chip label small :color="item.location.color_code">{{ item.location.name }}</v-chip>
            </template>

            <template v-slot:item.assignments="{ item }">

              <v-select 
                v-model="item.users" 
                :items="filterUsersAvailability(item, teamUsers)" 
                :readonly="!$can('manage_schedules')"
                hide-details 
                multiple 
                dense
                class="no-border"
                return-object 
                item-text="name" 
                item-value="id" 
                :id="'shift'+item.id"
                :menu-props="{ bottom: true, offsetY: true }"
              >

                <!-- NUMBER OF SHIFT ASSIGNMENTS -->
                <template v-slot:prepend-inner>
                  <v-chip 
                    small 
                    dark 
                    label 
                    :color="checkMax(item.max_participants, item.users.length)"
                  >
                    {{ item.users.length }}/{{ item.max_participants }}
                  </v-chip>
                </template>

                <!-- SELECTION SLOT: DISPLAYED IN TEXTBOX -->
                <template v-slot:selection="data">
                  <v-chip 
                    small 
                    label 
                    v-bind="data.attrs" 
                    :input-value="data.selected" 
                    :color="item.users[data.index].pivot.status !== undefined ? shiftStatus[item.users[data.index].pivot.status].color : ''"
                  >
                    {{ data.item.name}}
                    
                    <v-icon 
                      small 
                      right
                      v-if="item.users[data.index].pivot.status < 2"
                      @click.stop="updateStatus(data, item, 2)"
                    >
                      mdi-thumb-up-outline
                    </v-icon>
                  </v-chip>
                </template>


                <!-- ITEM SLOT: DISPLAYED IN DROPDOWN LIST -->
                <template v-slot:item="data">
                  <v-list-item dense>
                    <v-list-item-avatar class="ma-0">
                      <v-icon small :color="data.attrs['aria-selected']==='true' ? 'green' : 'red'">mdi-checkbox-blank-circle</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-content @click="addShiftUser(data, item)">
                      <v-list-item-title>{{ data.item.name }}</v-list-item-title>
                      <v-list-item-subtitle></v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>
                </template>

              </v-select>

            </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios'
import { helper, scheduling } from '~/mixins/helper'


export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, scheduling],
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
      shiftTable_key: 1,
      schedData: [],
      shiftData: [],
      shiftHeaders: [
        { text: this.$t('shifts.shift_time'), align: 'start', value: 'day' },
        { text: this.$t('shifts.location'), value: 'location', align: 'center'},
        { text: this.$t('shifts.assignments'), value: 'assignments', align: 'start', sortable: false, width: '60%'},
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
          this.date = this.$dayjs(this.schedData.date_start).format("YYYY-MM-DD")

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
      var status = this.team.setting_shift_assignment_autoaccept ? 2 : 0

      if (attrs['aria-selected']==='true') {
        this.removeShiftUser(data, shift)

      } else {

        await axios({
          method: 'post',      
          url: '/api/schedules/joinshift',
          data: {
            user_id: user.id,
            shift_id: shift.id,
            status: status
          }
        })
        .then(response => {
          user.pivot.status = status
          shift.users = response.data.shiftusers
        })
      }

    },


    async updateStatus (data, shift, status) {
      var user = data.item

      await axios({
        method: 'post',      
        url: '/api/schedules/shiftuserstatus',
        data: {
          user_id: user.id,
          shift_id: shift.id,
          status: status
        }
      })
      .then(response => {
        user.pivot.status = status
        shift.users = response.data.shiftusers
      })

    },


    async approveAllRequests() {
      if (await this.$root.$confirm(this.$t('schedules.confirm_approve_all'), null, 'error')) {
        await axios({
          method: 'get',      
          url: '/api/schedules/' + this.id + '/approveall',
        })
        .then(response => {
          this.showSnackbar(this.$t('general.info_updated'), 'success')
          this.getShiftData()
          this.shiftTable_key += 1
        })
      }
    },


    checkMax (target, actual) {
        var color = 'grey darken-3'

        if (target === actual) {
          color = 'green'
        } else if (target < actual) {
          color="red darken-4"
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