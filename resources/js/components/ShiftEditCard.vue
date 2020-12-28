<template>
  <div ref="mainDiv">
    <v-card :id="shift.id" class="mt-5 handle">
      
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
            <div class="text-h6 shift-title">{{ shift.location.name }}</div>
            <div v-if="!isTemplate">{{ $dayjs(shift.time_start).format('ddd, L') }}</div>
            <div>{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</div>
          </v-col>
        </v-row>
      </v-card-subtitle>


      <v-card-text class="text-center pa-0" :style="'background-color: ' + (shift.location.color_code != null ? shift.location.color_code : '')">
        <v-row dense>
          <v-col cols=3 class="pa-1 text-center">
            <v-icon color="white">
              {{ shift.mandatory ? 'mdi-heart' : 'mdi-heart-outline' }}
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
          <v-row v-for="user in filterShiftUsers(shift.users)" :key="user.id" :title="shiftStatus[user.pivot.status].text">
            <v-col cols=9 class="ml-3 pa-0">
              <v-icon small class="mr-2" :color="shiftStatus[user.pivot.status].color">
                {{ shiftStatus[user.pivot.status].icon }}
              </v-icon>
              <span :class="(shiftStatus[user.pivot.status].color + '--text ') + (user.pivot.status == 3 ? 'text-decoration-line-through' : '')">
                {{ user.name }}
              </span>
            </v-col>
            <v-col cols=1 class="pa-0">
              <v-icon small v-if="user.driver">mdi-car</v-icon>
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
                  <v-icon small>mdi-account-multiple }}</v-icon>
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
                  <v-icon small>mdi-pencil</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('schedules.new_shift') }}</span>
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
                  <v-icon small>mdi-delete</v-icon>
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
                  <v-icon small>mdi-clipboard-arrow-down</v-icon>
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
                  <v-icon small>mdi-content-duplicate</v-icon>
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
      return this.team_availability.length > 0
    }
  },

  methods: {

    async duplicateShift (id, makeSubsequent) {
      var newShiftData = []

      await axios.get('/api/schedules/' + this.schedule.id + '/shifts/' + id)
        .then(response => {
          newShiftData = response.data

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
            this.storeSchedule(response.data.schedule)
            this.$emit('update')
          })
        })
    },
    
    async deleteShift (id) {
      if (await this.$root.$confirm(this.$t('schedules.confirm_delete_shift'), null, 'error')) {
        await axios.delete('/api/schedules/' + this.schedule.id + '/shifts/' + id)
          .then(response => {
            this.showSnackbar(this.$t('schedules.success_delete_shift'), 'success')
            this.storeSchedule(response.data.schedule)
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
  font-size: 1.1rem !important;
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
</style>