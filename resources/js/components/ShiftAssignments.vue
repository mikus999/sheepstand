<template>
  <v-card app>
    <v-toolbar
      dark
      color="primary" 
    >
      <v-toolbar-title>{{ $t('schedules.assignments') }}</v-toolbar-title>

      <v-spacer />

      <span class="mr-2 text-overline">(<v-icon small class="mr-1">mdi-keyboard</v-icon>ESC)</span>
      <v-btn icon dark @click="$emit('close')">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-toolbar>

    <v-card-title class="my-4">
      <v-btn fab x-large class="location-avatar" @click="showLocationOverlay">
        {{ shift.location.name.substring(0, 1) }}
      </v-btn>

      <span class="ml-8">
        <div class="text-h6">{{ shift.location.name }}</div>
        <div>{{ $dayjs(shift.time_start).format('ddd, L') }}</div>
        <div>{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</div>
      </span>
    </v-card-title>



    <v-data-table
      :headers="headers"
      :items="sortUsers || []"
      disable-pagination
      hide-default-footer
    >
      <template v-slot:item.action="{ item }">
        <v-btn v-if="!isShiftMember(item.id)" icon @click="addShiftUser(item)">
          <v-icon>mdi-plus-box</v-icon>
        </v-btn>

        <v-btn v-else icon @click="removeShiftUser(item)" :color="shiftStatus[userShiftStatus(item.id)].color">
          <v-icon>mdi-minus-box</v-icon>
        </v-btn>
      </template>


      <template v-slot:item.shift_status="{ item }">
        <v-chip v-if="isShiftMember(item.id) || userShiftStatus(item.id) == 3" 
          label 
          small 
          :color="shiftStatus[userShiftStatus(item.id)].color"
        >
          {{ shiftStatus[userShiftStatus(item.id)].text }}
        </v-chip>
      </template>


      <template v-slot:item.fts_status="{ item }">
        {{ ftsStatus[item.fts_status].text }}
      </template>


      <template v-slot:item.shift_counts="{ item }">
        {{ item.shifts_7days }} / {{ item.shifts_14days }} / {{ item.shifts_30days }}
      </template>


      <template v-slot:item.conflicts="{ item }">
        <span class="red--text">
          {{ getConflictMessage(checkShiftConflicts(shift, getUserShifts(item.id), true, false)) }}
        </span>
      </template>
    </v-data-table>

    <!--
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
    -->

    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
      <Leaflet :location="shift.location" :fill="shift.location.color_code" :width="mapWidth" height="500px" readonly 
          v-on:close="locationOverlay = false" v-on:click.native.stop/>
    </v-overlay>
  </v-card>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling } from '~/mixins/helper'
import Leaflet from '~/components/Leaflet.vue'

export default {
  name: 'ShiftAssignments',
  mixins: [helper, scheduling],
  props: {
    shift: {
      type: [Object, Array]
    },
  },

  components: {
    Leaflet
  },
  
  data() {
    return {
      locationOverlay: false,
      team_users_avail: [],
      showAvailUsers: true,
      headers: [
        { 
          text: '',
          value: 'action', 
          align: 'center', 
          sortable: false
        },      
        { 
          text: this.$t('general.status'),
          value: 'shift_status', 
          align: 'center', 
        },    
        { 
          text: this.$t('general.name'), 
          value: 'name', 
          align: 'start', 
        },
        { 
          text: this.$t('account.fts_status'), 
          value: 'fts_status', 
          align: 'start',
        },
        {
          text: this.$t('shifts.shifts_7_14_30'), 
          value: 'shift_counts', 
          align: 'center',
        },
        {
          text: this.$t('shifts.conflicts'), 
          value: 'conflicts', 
          align: 'start',
        },
      ],
    }
  },

  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      team_availability: 'scheduling/team_availability',
      team_users: 'scheduling/team_users',
    }),

    mapWidth() {
      var newWidth = this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
      return newWidth
    },

    sortUsers() {
      var tempArr = this.showAvailUsers ? this.team_users_avail : this.team_users
      tempArr.sort((a,b) => {
        return this.isShiftMember(a.id) ? -1 : 1
      })
      return tempArr
    }
  },

  created() {
    this.team_users_avail = this.getAvailableUsers()
  },

  methods: {
    isShiftMember(userid) {
      var tempArr = this.shift.users.filter(u => 
        u.id == userid &&
        u.pivot.status != 3
      )
      return tempArr.length > 0
    },


    userShiftStatus(userid) {
      var tempArr = this.shift.users.filter(u => u.id == userid)
      return tempArr.length > 0 ? tempArr[0].pivot.status : -1
    },

    getUserShifts(userid) {
      var tempArr = this.team_users.filter(u => u.id == userid)
      if (tempArr.length > 0) {
        return tempArr[0].shifts
      } else {
        return []
      }
    },


    getAvailableUsers() {
      return this.filterUsersAvailability(this.shift, this.team_users, this.team_availability)
    },

    async removeShiftUser (user) {
      await axios({
        method: 'post',      
        url: '/api/schedules/leaveshift',
        data: {
          user_id: user.id,
          shift_id: this.shift.id,
        }
      })
      .then(response => {
        this.shift.users = response.data.shiftusers
        this.storeTeamUsers(response.data.teamusers)
        this.team_users_avail = this.getAvailableUsers()
      })
    },


    async addShiftUser (user) {
      var status = this.team.setting_shift_assignment_autoaccept ? 2 : 0

      await axios({
        method: 'post',      
        url: '/api/schedules/joinshift',
        data: {
          user_id: user.id,
          shift_id: this.shift.id,
          status: status
        }
      })
      .then(response => {
        user.pivot.status = status
        this.shift.users = response.data.shiftusers
        this.storeTeamUsers(response.data.teamusers)
        this.team_users_avail = this.getAvailableUsers()
      })

    },

    showLocationOverlay() {
      this.locationOverlay = true
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

  },
}
</script>


<style scoped>
  .location-avatar
  {
    font-size: 2.5rem;
  }
</style>