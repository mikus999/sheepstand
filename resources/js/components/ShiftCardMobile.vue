<template>

  <v-expansion-panel>
    <v-expansion-panel-header class="py-0" disable-icon-rotate>
      <v-col cols="2" class="pa-0">
        <v-btn fab small class="location-avatar" :color="shift.location.color_code" @click.stop="$emit('location')">
          {{ shift.location.name.substring(0, 1) }}
        </v-btn>
      </v-col>

      <v-col class="shift-subtitle">
        <div class="shift-title mb-2">
          {{ shift.time_start | formatDay }}<br>
          {{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}<br>
        </div>
        {{ shift.location.name }}
      </v-col>

      <template v-slot:actions>
        <span v-if="hasConflicts">
          <v-icon color="red">{{ icons.mdiAccountAlert }}</v-icon>
        </span>

        <span v-else-if="isShiftMember">
          <v-icon :color="getShiftStatusLookup(shift, user).color">
            {{ getShiftStatusLookup(shift, user).icon }}
          </v-icon>
        </span>

        <span v-else-if="shift.schedule.status == 1">
          <v-avatar size="23" :color="getNumberOpenSpots > 0 ? 'primary' : 'grey'">
            <span class="white--text font-weight-bold pa-0">{{ getNumberOpenSpots }}</span>
          </v-avatar>
        </span>

        <span v-else>
          <v-icon>{{ icons.mdiLock }}</v-icon>
        </span>

        <span><v-icon>{{ icons.mdiChevronDown }}</v-icon></span>
      </template>

    </v-expansion-panel-header>

    <v-expansion-panel-content>
      <v-system-bar :color="$vuetify.theme.dark ? '#1c1c1c' : '#ffffff'">
        <MarqueeText v-if="hasConflicts">
            <span v-for="(conflict, index) in conflicts" :key="index" class="warning-text">
              <v-icon color="red" class="ml-6">{{ icons.mdiAlert }}</v-icon>
              {{ getConflictMessage(conflict) }}
            </span>
        </MarqueeText>
      </v-system-bar>

      <div class="shift-subtitle">
        <span class="text-overline">{{ $t('teams.team_name') }}: </span>
        {{ shift.schedule.team.display_name }}
      </div>

      <div class="text-overline">{{ $t('shifts.participants') }}</div>

      <div v-if="filterShiftUsers(shift.users).length > 0">
        <div v-for="user in filterShiftUsers(shift.users)" :key="user.id" class="ma-2 list-participants" :title="getShiftStatus(user.pivot.status).text" disabled>
          <v-icon small class="ml-n2 mr-2" :color="getShiftStatus(user.pivot.status).color">
            {{ getShiftStatus(user.pivot.status).icon }}
          </v-icon>
          <span :class="(getShiftStatus(user.pivot.status).color + '--text ') + (user.pivot.status == 3 ? 'text-decoration-line-through' : '')">
            {{ user.name }}
          </span>
        </div>
      </div>

      <div v-if="shift.schedule.status == 1">
        <div v-for="n in getNumberOpenSpots" :key="n" class="ma-2" disabled>
          <div class="ml-n2 dashed-border rounded list-participants" width="100%">
            <v-icon small class="ml-1 mr-2" color="grey">{{ icons.mdiAccountOutline }}</v-icon>
            <span>{{ $t('general.available') }}</span>
          </div>
        </div>
      </div>

      <div v-else-if="filterShiftUsers(shift.users).length == 0">
        <span class="ma-2 list-participants">{{ $t('shifts.no_participants') }}</span>
      </div>


      <v-row>
        <v-col class="text-center">
          <ShiftStatusButton :shift="shift" />
        </v-col>
      </v-row>

    </v-expansion-panel-content>
   </v-expansion-panel>
</template>


<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling, messages } from '~/mixins/helper'
import MarqueeText from 'vue-marquee-text-component'
import ShiftStatusButton from '~/components/ShiftStatusButton.vue'

export default {
  name: 'ShiftCard',
  mixins: [helper, scheduling],
  components: {
    MarqueeText,
    ShiftStatusButton
  },
  props: {
    shift: {
      type: [Object, Array]
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '230px'
    },
  },

  data () {
    return {
      locationOverlay: false,
    }
  },


  computed: {
    ...mapGetters({
      shift_conflicts: 'scheduling/shift_conflicts'
    }),
    
    mapWidth() {
      return this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
    },

    hasConflicts() {
      return this.conflicts.length > 0
    },
    
    isShiftMember() {
      var temp = this.shift.users.map(o => o['id'])
      var index = temp.indexOf(this.user.id)
      return index > -1
    },

    getNumberOpenSpots() {
      return this.returnZero(this.shift.max_participants - this.filterShiftUsers(this.shift.users).length)
    },

    isFinalized() {
      return this.shift.schedule.status == 2
    },

    conflicts() {
      var result = []
      if (this.shift_conflicts != null) {
        result = this.shift_conflicts.filter(c => c.shift == this.shift.id)

        if (result[0] != undefined) {
          result = result[0].conflicts
        } 
      }

      return result
    }
  },

  methods: {

    dayOfWeek (daynum) {
      var days = []
      days = this.$dayjs().localeData().weekdaysShort()
      days[7] = days[0]
      return days[daynum]
    },


    getConflictMessage(conflict) {
      var result = ''

      if (conflict.type == 'conflict') {
        result += this.$t('shifts.conflict_shift_another_team')
        this.team.display_name == conflict.team ? '' : result += ' (' + conflict.team + ')'
      } else if (conflict.type == 'adjacent') {
        result += this.$t('shifts.conflict_shift_adjacent')
        this.team.display_name == conflict.team ? '' : result += ' (' + conflict.team + ')'
      }

      return result
    },



    filterShiftUsers(shiftUsers) {
      return shiftUsers.filter(u => u.pivot.status != 3)
    },


    returnZero (n) {
      return n < 0 ? 0 : n
    }


  }

}
</script>


<style scoped>
  .location-avatar
  {
    font-size: 1.5rem;
  }

  .location-avatar-xs
  {
    font-size: 1.2rem;
  }

  .shift-title
  {
    font-size: .9rem !important;
    font-weight: bold;
    line-height: 1.25;
  }

  .shift-subtitle
  {
    font-size: .8rem !important;
  }

  .list-participants
  {
    font-size: .75rem;
  }

  .switch-label
  {
    font-size: .85rem !important;
  }

  .dashed-border 
  {
    border-style: dashed;
    border-color: #9E9E9E;
    border-width: thin;
  }
</style>