<template>
  <div ref="mainDiv">
    <v-card :id="shift.id" class="mt-5 handle" :disabled="!checkAssignmentTrade">
      
      <v-card-subtitle class="text-center font-weight-bold" :style="'background-color: ' + (shift.location.color_code != null ? shift.location.color_code : '')">
        <v-row class="align-center">
          <v-col cols=3 class="pa-0">
            <v-btn 
              fab 
              :small="$vuetify.breakpoint.smAndDown"
              :class="$vuetify.breakpoint.smAndDown ? 'location-avatar-sm' : 'location-avatar'" 
              @click="$emit('location')"
            >
              {{ shift.location.name.substring(0, 1) }}
            </v-btn>
          </v-col>
          <v-col cols=9 class="pa-0 shift-subtitle">
            <div class="text-h5 shift-title">{{ shift.location.name }}</div>
            <div v-if="!isTemplate">{{ $dayjs(shift.time_start).format('ddd, L') }}</div>
            <div>{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</div>
          </v-col>
        </v-row>
      </v-card-subtitle>


      <v-card-text class="text-center pa-0" :style="'background-color: ' + (shift.location.color_code != null ? shift.location.color_code : '')">
        <v-row dense>
          <v-col cols=3 class="pa-1 text-center">
            <v-icon color="white">
              {{ shift.mandatory ? icons.mdiHeart : icons.mdiHeartOutline }}
            </v-icon>
          </v-col>

          <v-col cols=3 offset=1 class="pa-0">
            <v-chip x-small>{{ shift.min_participants }}</v-chip><br>
            <span class="chip-participants--label">{{ $t('general.min') }}</span>
          </v-col>
          
          <v-col cols=3 class="pa-0">
            <v-chip x-small>{{ shift.max_participants }}</v-chip><br>
            <span class="chip-participants--label">{{ $t('general.max') }}</span>
          </v-col>
        </v-row>
      </v-card-text>
      

      <v-card-text v-if="!isTemplate" class="text-left list-participants ma-2 pa-0">
        <div v-if="hasParticipants">
          <v-row v-for="user in filterShiftUsers(shift.users)" 
            :key="user.id" 
            :title="getShiftStatus(user.pivot.status).text" 
            @click="selectUser(user)"
          >
            <v-col cols=9 class="ml-3 pa-0">
              <v-avatar size="20" class="mr-1" :color="user.fts_status > 0 ? getShiftStatus(user.pivot.status).color : ''">
                <v-icon small :color="user.fts_status == 0 ? getShiftStatus(user.pivot.status).color : 'white'">
                  {{ getShiftStatus(user.pivot.status).icon }}
                </v-icon>
              </v-avatar>
              <v-hover v-slot:default="{ hover }">
                <span :class="(getShiftStatus(user.pivot.status).color + '--text ') + 
                  (user.pivot.status == 3 ? 'text-decoration-line-through ' : ' ') + 
                  (assignment_trade == user ? 'selected-user ' : ' ') +
                  (hover ? 'hover-user ' : ' ')"
                >
                  {{ user.name }}
                </span>
              </v-hover>
            </v-col>
            <v-col cols=1 class="pa-0">
              <v-icon small v-if="user.driver">{{ icons.mdiCar }}</v-icon>
            </v-col>
          </v-row>
        </div>

        <div v-else>
          {{ $t('shifts.no_participants')}}  
        </div>
      </v-card-text>


      <v-divider class="pa-0 ma-0" />

      <v-card-actions class="pa-0">
        <v-row dense>
          <v-col v-if="!isTemplate">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  small
                  @click="showParticipantDialog()"
                  v-bind="attrs"
                  v-on="on"
                  :disabled="!assignmentsLoaded"
                  :loading="!assignmentsLoaded"
                >
                  <v-icon small>{{icons.mdiAccountMultiple }}</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('shifts.participants') }}</span>
            </v-tooltip>
          </v-col>
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  small
                  @click="showShiftDialog()"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon small>{{ icons.mdiPencil }}</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('general.edit') }}</span>
            </v-tooltip>
          </v-col>
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  small
                  @click="deleteShift(shift.id)"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon small>{{ icons.mdiDelete }}</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('general.delete') }}</span>
            </v-tooltip>
          </v-col>
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  small
                  @click="duplicateShift(shift.id, true)"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon small>{{ icons.mdiClipboardArrowDown }}</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('shifts.make_next_shift') }}</span>
            </v-tooltip>
          </v-col>        
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  small
                  @click="duplicateShift(shift.id, false)"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon small>{{ icons.mdiContentDuplicate }}</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('general.duplicate') }}</span>
            </v-tooltip>
          </v-col>
        </v-row>
      </v-card-actions>



      <v-dialog :value="dialog" @click:outside="closeShiftDialog()" width="500px">
        <ShiftNewCard :shift="shift" edit v-on:update="$emit('update')" v-on:close="closeShiftDialog()" />
      </v-dialog>


      <v-dialog fullscreen v-model="participantDialog">
        <ShiftAssignments :shift="shift" :team_availability="team_availability" v-on:close="closeParticipantDialog" />
      </v-dialog>

    </v-card>

  </div>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling } from '~/mixins/helper'
import ShiftNewCard from '~/components/ShiftNewCard.vue'
import ShiftAssignments from '~/components/ShiftAssignments.vue'

export default {
  name: 'ShiftEditCard',
  mixins: [helper, scheduling],
  components: {
    ShiftNewCard,
    ShiftAssignments,
  },
  props: {
    shift: {
      type: [Object, Array]
    },
    team_availability: {
      type: [Object, Array]
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '170px'
    },
    onlyinfo: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      dialog: false,
      participantDialog: false,
      showSwapMessage: false,
      mandatoryIcon: {
        position: 'absolute',
        top: '5px',
        left: '5px',
        zIndex: '501'
      }
    }
  },

  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      team_users: 'scheduling/team_users',
    }),


    isTemplate () {
      return this.schedule.status == 9
    },

    hasParticipants() {
      return this.filterShiftUsers(this.shift.users).length > 0
    },

    assignmentsLoaded() {
      return this.team_availability ? this.team_availability.length > 0 : false
    },

    checkAssignmentTrade() {
      var result = true

      if (this.assignment_trade != null && this.assignment_trade.pivot.shift_id != this.shift.id) {

        // Check if user is available for shift
        var tempUser = this.team_availability.filter(ua => ua.id == this.assignment_trade.id)
        if (tempUser.length > 0) {
          result = this.checkShiftAvailability(this.shift, tempUser[0])
        }

        // Check if user is already in shift
        if (result) {
          var temp = this.shift.users.filter(su => su.id == this.assignment_trade.id)
          result = temp.length == 0
        }
      }

      return result
    }

  },

  methods: {

    async duplicateShift (id, makeSubsequent) {
      var newShiftData = []

      await axios.get('/api/schedules/' + this.schedule.id + '/shifts/' + id)
        .then(response => {
          newShiftData = response.data.data.shift

          // This will make the new shift immediately follow the source shift
          if (makeSubsequent) {
            newShiftData.time_start = newShiftData.time_end
            newShiftData.time_end = this.$dayjs(newShiftData.time_start).add(this.team.default_shift_minutes, 'm').format('YYYY-MM-DD HH:mm:ss')
          }

          axios({
            method: 'post',      
            url: '/api/schedules/' + this.schedule.id + '/shifts',
            data: {
              location_id: newShiftData.location_id,
              time_start: newShiftData.time_start,
              time_end: newShiftData.time_end,
              min_participants: newShiftData.min_participants,
              max_participants: newShiftData.max_participants,
              mandatory: newShiftData.mandatory
            }
          })
          .then(response => {
            this.storeSchedule(response.data.data.schedule)
            this.$emit('update')
          })
        })
    },
    
    async deleteShift (id) {
      if (await this.$root.$confirm(this.$t('schedules.confirm_delete_shift'), null, 'error')) {
        await axios.delete('/api/schedules/' + this.schedule.id + '/shifts/' + id)
          .then(response => {
            this.showSnackbar(this.$t('schedules.success_delete_shift'), 'success')
            this.storeSchedule(response.data.data.schedule)
            this.$emit('update')
          })

      }
    },

    filterShiftUsers(shiftUsers) {
      return shiftUsers.filter(u => u.pivot.status != 3)
    },

    showShiftDialog () {
      this.dialog = true
    },

    closeShiftDialog () {
      this.dialog = false
    },


    selectUser (item) {
      var currUser = this.assignment_trade

      if (currUser == null) {
        // Select a user
        this.storeAssignmentTrade(item)
        this.showSwapMessage = true
        this.$emit('swapmessage', true)

      } else if (currUser.id == item.id) {
        // Deselect user
        this.storeAssignmentTrade(null)
        this.showSwapMessage = false
        this.$emit('swapmessage', false)

      } else {
        // Make trade
        this.switchAssignments(currUser, item)
        this.showSwapMessage = false
        this.$emit('swapmessage', false)

      }
      
    },


    async switchAssignments (user1, user2) {
      // TODO Need to check if users are already part of new shifts. If so, cancel.


      await axios({
        method: 'post',      
        url: '/api/assignments/switch',
        data: {
          schedule_id: this.schedule.id,
          team_id: this.team.id,
          user1_id: user1.id,
          user2_id: user2.id,
          shift1_id: user1.pivot.shift_id,
          shift2_id: user2.pivot.shift_id,
          status1: user1.pivot.status,
          status2: user2.pivot.status
        }
      })
      .then(response => {
        this.storeAssignmentTrade(null)
        this.showSnackbar(this.$t('general.info_updated'), 'success')
        this.storeSchedule(response.data.data.schedule)
        this.$emit('update')
      })
    },


    showParticipantDialog() {
      this.participantDialog = true
    },

    closeParticipantDialog() {
      this.participantDialog = false
    },

    returnZero (n) {
      return n < 0 ? 0 : n
    },
  }
}
</script>


<style scoped>
.location-avatar
{
  font-size: 2.5rem;
}

.location-avatar-sm
{
  font-size: 1.5rem;
}

.shift-title
{
  font-size: 1.0rem !important;
  font-weight: bold;
}

.shift-subtitle
{
  font-size: .8rem !important;
}

.list-participants
{
  font-size: .75rem;
}

.chip-participants--label
{
  font-size: .6rem;
}

.hover-user
{
  cursor: pointer;
  text-decoration-line: underline;
  text-decoration-style: dashed;
}

.selected-user
{
  cursor: pointer;
  background-color: yellow;
}
</style>