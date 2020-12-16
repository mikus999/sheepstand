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

              <template v-slot:item.location="{ item }">
                <v-chip label small :color="item.location.color_code">{{ item.location.name }}</v-chip>
              </template>

              <template v-slot:item.assignments="{ item }">

                <v-select 
                  :value="item.users" 
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
                  :key="shift_key + item.id"
                >

                  <!-- NUMBER OF SHIFT ASSIGNMENTS -->
                  <template v-slot:prepend-inner v-if="$vuetify.breakpoint.smAndUp">
                    <v-chip 
                      small 
                      dark 
                      label 
                      :color="checkMax(item.max_participants, filterShiftUsers(item.users).length)"
                    >
                      {{ filterShiftUsers(item.users).length }}/{{ item.max_participants }}
                    </v-chip>
                  </template>

                  <!-- SELECTION SLOT: DISPLAYED IN TEXTBOX -->
                  <template v-slot:selection="shift_user">
                    <v-chip 
                      small 
                      label 
                      v-bind="shift_user.attrs" 
                      :input-value="shift_user.selected" 
                      :color="item.users[shift_user.index].pivot.status !== undefined ? shiftStatus[item.users[shift_user.index].pivot.status].color : ''"
                      v-if="item.users[shift_user.index].pivot.status != 3"
                    >
                      <v-icon
                        small
                        left
                        v-if="checkShiftConflicts(item, shift_user.item.shifts, true, false).length > 0"
                      >
                        mdi-alert
                      </v-icon>

                      {{ shift_user.item.name}}
                      
                      <v-icon 
                        small 
                        right
                        color="green darken-2"
                        v-if="item.users[shift_user.index].pivot.status < 2"
                        @click.stop="updateStatus(shift_user, item, 2)"
                        class="ml-2"
                      >
                        mdi-thumb-up
                      </v-icon>

                      <v-icon 
                        small 
                        right
                        color="black"
                        v-if="item.users[shift_user.index].pivot.status == 0"
                        @click.stop="removeShiftUser(shift_user, item)"
                        class="ml-3"
                      >
                        mdi-close-circle
                      </v-icon>

                      <v-icon 
                        small 
                        right
                        color="black"
                        v-if="item.users[shift_user.index].pivot.status == 1"
                        @click.stop="updateStatus(shift_user, item, 3)"
                        class="ml-3"
                      >
                        mdi-thumb-down
                      </v-icon>
                    </v-chip>
                  </template>


                  <!-- ITEM SLOT: DISPLAYED IN DROPDOWN LIST -->
                  <template v-slot:item="shift_user">
                    <v-list-item dense @click="addShiftUser(shift_user, item)" :disabled="getStatus_List(shift_user, item) == 3">
                      <v-list-item-avatar class="ma-0">
                        <v-icon small :color="(getStatus_List(shift_user, item) != 3 && shift_user.attrs['aria-selected']==='true') ? 'green' : 'red'">mdi-checkbox-blank-circle</v-icon>
                      </v-list-item-avatar>
                      <v-list-item-content>
                        <v-list-item-title :class="getStatus_List(shift_user, item) == 3 ? 'text-decoration-line-through' : ''">{{ shift_user.item.name }}</v-list-item-title>
                        <v-list-item-subtitle class="red--text">
                          <span v-html="getConflictMessage(checkShiftConflicts(item, shift_user.item.shifts, true, false))"></span>
                        </v-list-item-subtitle>
                      </v-list-item-content>
                    </v-list-item>
                  </template>

                </v-select>

              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
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
      shift_key: 1,
      shiftHeaders: [
        { 
          text: this.$t('shifts.shift_time'), 
          value: 'time_start', 
          align: 'start', 
          sort: (a, b) => this.$dayjs(a).isoWeekday() - this.$dayjs(b).isoWeekday() 
        },
        { 
          text: this.$t('shifts.location'), 
          value: 'location', 
          align: 'center'
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
    initialize () {
      this.getSchedule()
      this.getTeamUsers()
    },

    async getSchedule () {
      await axios.get('/api/schedules/show/' + this.id)
        .then(response => {
          this.storeSchedule(response.data)
          this.storeShifts(response.data.shifts)

          this.date = this.$dayjs(response.data.date_start).format("YYYY-MM-DD")
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
        this.teamUsers = response.data.teamusers
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
          this.teamUsers = response.data.teamusers
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