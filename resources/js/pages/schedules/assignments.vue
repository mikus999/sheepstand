<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('schedules.schedule')"></PageTitle>
    </v-row>

    <v-card width="100%">
      <v-card-text>

        <v-row>
          <v-col xs=1 sm=4 class="text-left" >
            <v-btn text :x-large="$vuetify.breakpoint.smAndUp" @click="$router.go(-1)">
              <v-icon left>mdi-arrow-left</v-icon>
              <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('general.go_back')}}</span>
            </v-btn>
          </v-col>

          <v-col xs=10 sm=4 class="text-center">
            <span class="text-h6">{{ $t('schedules.week_of')}} {{ schedule.date_start | formatDate }}</span>
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
            <v-data-table 
              :headers="shiftHeaders" 
              :items="shifts ? shifts : []" 
              :key="shiftTable_key" 
              :loading="pageLoad"
              sort-by="time_start"
              multi-sort
            >
              <template v-slot:top>
                <v-toolbar flat>
                  <v-toolbar-title v-if="$vuetify.breakpoint.smAndUp">{{ $t('schedules.assignments') }}</v-toolbar-title>
                  <v-spacer />
                  
                  <v-btn
                    color="deep-orange"
                    class="ml-2"
                    @click="approveAllRequests(0)"
                    v-if="hasPendingAssignments"
                  >
                    <v-icon small left>mdi-thumb-up</v-icon>
                    <span>{{ $vuetify.breakpoint.xs ? $t('general.all') : $t('schedules.approve_all_assignments') }}</span>
                  </v-btn>

                  <v-btn
                    color="grey"
                    class="ml-2"
                    @click="approveAllRequests(1)"
                    v-if="hasPendingRequests"
                  >
                    <v-icon small left>mdi-thumb-up</v-icon>
                    <span>{{ $vuetify.breakpoint.xs ? $t('general.all') : $t('schedules.approve_all_requests') }}</span>
                  </v-btn>
                </v-toolbar>
              </template>

              <template v-slot:item.time_start="{ item }">
                {{ item.time_start | formatDay }}<br />
                {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
              </template>

              <template v-slot:item.location_id="{ item }">
                <v-chip label small :color="item.location.color_code">{{ item.location.name }}</v-chip>
              </template>


              <template v-slot:item.participants="{ item }">
                <v-chip small dark label 
                  :color="checkMax(item.max_participants, filterShiftUsers(item.users).length)"
                >
                  {{ filterShiftUsers(item.users).length }}/{{ item.max_participants }}
                </v-chip>
              </template>


              <template v-slot:item.assignments="{ item }">

                <v-chip-group column>
                  <v-btn icon @click="openParticipantDialog(item)" class="mr-2">
                    <v-icon>mdi-account-multiple-plus</v-icon>
                  </v-btn>
                  
                  <v-chip small label 
                    v-for="shift_user in item.users"
                    :key="shift_user.id"
                    :color="shift_user.pivot.status !== undefined ? shiftStatus[shift_user.pivot.status].color : ''"
                  >
                    <v-tooltip
                      bottom
                      color="red"
                      v-if="checkShiftConflicts(item, getUserShifts(shift_user.id), true, false).length > 0"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-icon small left v-bind="attrs" v-on="on">mdi-alert</v-icon>
                      </template>
                      <span>{{ getConflictMessage(checkShiftConflicts(item, getUserShifts(shift_user.id), true, false)) }}</span>
                    </v-tooltip>

                    {{ shift_user.name}}
                  
                    <v-icon 
                      small 
                      right
                      color="green darken-2"
                      v-if="shift_user.pivot.status < 2"
                      @click.stop="updateStatus(shift_user, item, 2)"
                      class="ml-2"
                    >
                      mdi-thumb-up
                    </v-icon>

                    <v-icon 
                      small 
                      right
                      color="black"
                      v-if="shift_user.pivot.status == 0"
                      @click.stop="removeShiftUser(shift_user, item)"
                      class="ml-3"
                    >
                      mdi-close-circle
                    </v-icon>

                    <v-icon 
                      small 
                      right
                      color="black"
                      v-if="shift_user.pivot.status == 1"
                      @click.stop="updateStatus(shift_user, item, 3)"
                      class="ml-3"
                    >
                      mdi-thumb-down
                    </v-icon>
                  </v-chip>
                </v-chip-group>

              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card-text>


      <v-dialog fullscreen v-model="participantDialog">
        <ShiftAssignments :shift="tempShift" :teamUsers="teamUsers" v-on:close="closeParticipantDialog" />
      </v-dialog>
    </v-card>


    <v-overlay :value="pageLoad" :opacity=".9" class="text-center">
      <v-progress-circular
        indeterminate
        color="primary"
        size="64"
      ></v-progress-circular>
      <h1 class="mt-16 text-h4">{{ pageLoad_text }}</h1>
    </v-overlay>

  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling } from '~/mixins/helper'
import ShiftAssignments from '~/components/ShiftAssignments.vue'

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
    ShiftAssignments
  },

  data () {
    return {
      pageLoad: true,
      pageLoad_progress: 0,
      pageLoad_text: '',
      dialog: false,
      participantDialog: false,
      date: '',
      shiftTable_key: 1,
      shift_key: 1,
      search: '',
      shiftHeaders: [
        { 
          text: this.$t('shifts.shift_time'), 
          value: 'time_start', 
          align: 'start', 
          sort: (a, b) => this.$dayjs(a).isoWeekday() - this.$dayjs(b).isoWeekday() 
        },
        { 
          text: this.$t('shifts.location'), 
          value: 'location_id', 
          align: 'center',
        },
        {
          text: this.$t('shifts.participants'), 
          value: 'participants', 
          align: 'center',
        },
        { 
          text: this.$t('shifts.assignments'), 
          value: 'assignments', 
          align: 'start', 
          sortable: false,
          width: '60%'
        },
      ],
      teamUsers: [],
      shiftUsers: {},
      availability: [],
      tempShift: [],
    }
  },

  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      //shift_conflicts: 'scheduling/shift_conflits',
    }),

    hasPendingAssignments() {
      var result = this.shifts.filter(s => s.users.filter(u => u.pivot.status == 0).length > 0)
      return result.length > 0
    },

    hasPendingRequests() {
      var result = this.shifts.filter(s => s.users.filter(u => u.pivot.status == 1).length > 0)
      return result.length > 0
    },

  },

  created () {
    this.initialize()
  },

  methods: {
    async initialize () {
      await this.getSchedule()
      await this.getUserAvailability()
      await this.getTeamUsers()
      this.pageLoad_text = this.$t('schedules.loading_complete')
      this.pageLoad_progress = 100
      this.pageLoad = false
    },

    async getSchedule () {
      this.pageLoad_text = this.$t('schedules.loading_schedule')
      this.pageLoad_progress = 5

      await axios.get('/api/schedules/show/' + this.id)
        .then(response => {
          this.storeSchedule(response.data)
          this.storeShifts(response.data.shifts)
          this.date = this.$dayjs(response.data.date_start).format("YYYY-MM-DD")

          this.pageLoad_progress = 10
        })

    },


  
    async getUserAvailability (date) {
      this.pageLoad_text = this.$t('schedules.loading_availability')

      await axios.get('/api/teams/' + this.team.id + '/availability/')
        .then(response => {
          this.availability = response.data.users
          this.pageLoad_progress = 60
        })

    },


    async getTeamUsers (date) {
      this.pageLoad_text = this.$t('schedules.loading_users')

      await axios.get('/api/teams/' + this.team.id + '/users/')
        .then(response => {
          this.teamUsers = response.data
          this.pageLoad_progress = 80
        })

    },



    showAvailableUsers(shift) {
      this.pageLoad_text = this.$t('schedules.loading_calculations')

      var avail = this.filterUsersAvailability(shift, this.teamUsers, this.availability)
      return avail
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
      .then(response => {
        shift.users = response.data.shiftusers
        this.teamUsers = response.data.teamusers
      })
    },

    async addShiftUser (user, shift) {
      var status = this.team.setting_shift_assignment_autoaccept ? 2 : 0

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
        this.teamUsers = response.data.teamusers
      })

    },


    async updateStatus (user, shift, status) {

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


    async approveAllRequests(status) {
      var confirm_msg = null

      if (status == 0) {
        confirm_msg = this.$t('schedules.confirm_approve_all_assignments')
      } else if (status == 1) {
        confirm_msg = this.$t('schedules.confirm_approve_all_requests')
      }

      if (await this.$root.$confirm(confirm_msg, null, 'error')) {
        await axios({
          method: 'get',      
          url: '/api/schedules/' + this.id + '/approveall/' + status,
        })
        .then(response => {
          this.storeSchedule(response.data)
          this.storeShifts(response.data.shifts)
          this.showSnackbar(this.$t('general.info_updated'), 'success')
          this.shiftTable_key += 1
        })
      }
    },


    filterShiftUsers(shiftUsers) {
      return shiftUsers.filter(u => u.pivot.status != 3)
    },


    getUserShifts(user_id) {
      var tempArr = this.teamUsers.filter(u => u.id == user_id)
      if (tempArr.length > 0) {
        return tempArr[0].shifts
      } else {
        return []
      }
    },


    getStatus_List(data, shift) {
      var result = null
      var user = data.item
      var shiftUser = shift.users.filter(u => u.id == user.id)
      if (shiftUser.length > 0) {
        result = shiftUser[0].pivot.status
      }
      return result
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


    handleKeyPress(event) {
      console.log(event)
    },

    
    getConflictMessage(conflicts) {
      var result = ''

      if (conflicts.length > 0) {
        conflicts.forEach(conflict => {
          if (result != '') result += '<br>'

          if (conflict.type == 'conflict') {
            result += this.$t('shifts.conflict_shift_another_team')
            this.team.display_name == conflict.team ? '' : result += ' (' + conflict.team + ')'
          } else if (conflict.type == 'adjacent') {
            result += this.$t('shifts.conflict_shift_adjacent')
            this.team.display_name == conflict.team ? '' : result += ' (' + conflict.team + ')'
          }
        })
        
      }

      return result
    },



    openParticipantDialog(shift) {
      this.tempShift = shift
      this.participantDialog = true
    },

    closeParticipantDialog() {
      this.participantDialog = false
      this.tempShift = []
    }
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