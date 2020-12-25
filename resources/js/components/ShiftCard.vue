<template>
  <v-card :outlined="!onlyinfo" :width="width" elevation="2" min-width="250">

    <v-card-subtitle class="text-center font-weight-bold" :style="'background-color: ' + (shift.location.color_code != null ? shift.location.color_code : '')">
      <v-row class="align-center">
        <v-col cols=3 class="pa-1">
          <v-btn fab class="location-avatar" @click="showLocationOverlay">
            {{ shift.location.name.substring(0, 1) }}
          </v-btn>
        </v-col>
        <v-col cols=9 class="pa-1 shift-subtitle">
          <div class="text-h6 shift-title">{{ shift.location.name }}</div>
          <div>{{ $dayjs(shift.time_start).format('ddd, L') }}</div>
          <div>{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</div>
        </v-col>
      </v-row>
    </v-card-subtitle>


    <v-card-text :style="'overflow-y: auto; height: ' + height">
      <v-system-bar :color="$vuetify.theme.dark ? '#1c1c1c' : '#ffffff'">
        <MarqueeText v-if="hasConflicts">
            <span v-for="(conflict, index) in conflicts" :key="index" class="warning-text">
              <v-icon color="red" class="ml-6">mdi-alert-box</v-icon>
              {{ getConflictMessage(conflict) }}
            </span>
        </MarqueeText>

        <div v-else class="text-overline">{{ $t('shifts.participants') }}</div>
      </v-system-bar>

      <div v-for="user in filterShiftUsers(shift.users)" :key="user.id" class="ma-2 list-participants" :title="shiftStatus[user.pivot.status].text" disabled>
        <v-icon small class="ml-n1 mr-2" :color="shiftStatus[user.pivot.status].color">
          {{ shiftStatus[user.pivot.status].icon }}
        </v-icon>
        <span :class="(shiftStatus[user.pivot.status].color + '--text ') + (user.pivot.status == 3 ? 'text-decoration-line-through' : '')">
          {{ user.name }}
        </span>
      </div>

      <div v-for="n in returnZero(shift.max_participants - filterShiftUsers(shift.users).length)" :key="n" class="ma-2" disabled>
        <div class="ml-n2 dashed-border rounded list-participants" width="100%">
          <v-icon small class="ml-1 mr-2" color="grey">mdi-account-outline</v-icon>
          <span>{{ $t('general.available') }}</span>
        </div>
      </div>

    </v-card-text>


    <v-card-actions v-if="!onlyinfo" class="pa-0">

      <v-row>
        <v-col class="text-center">
          <ShiftStatusButton :shift="shift" />
        </v-col>
      </v-row>

      <!--
      <v-row dense>
        <v-col class="text-center">
          <v-btn icon @click.stop="showLocationOverlay" class="me-2" :disabled="shift.location.map === null">
            <v-icon>mdi-map-search</v-icon>
          </v-btn>
        </v-col>
        <v-col class="text-center">
          <v-btn :disabled="!showButton('request')" :color="(request && myShiftStatus) ? '' : ''" icon @click.stop="updateShiftUser" class='me-2'>
            <v-icon>{{ request ? 'mdi-account-minus' : 'mdi-account-plus' }}</v-icon>
          </v-btn>
        </v-col>
        <v-col class="text-center">
          <v-btn :disabled="!showButton('trade')" :color="trade ? 'blue' : ''" icon @click.stop="updateTrade">
            <v-icon>mdi-account-convert</v-icon>
          </v-btn>
        </v-col>
      </v-row>
      -->
    </v-card-actions>
    

    <v-card-actions v-else>
      <v-spacer></v-spacer>
      <v-btn color="primary" text v-on:click="$emit('close')">
        {{ $t('general.close' ) }}
      </v-btn>
    </v-card-actions>


    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
      <Leaflet :location="shift.location" :fill="shift.location.color_code" :width="mapWidth" height="500px" readonly 
          v-on:close="locationOverlay = false" v-on:click.native.stop/>
    </v-overlay>
  </v-card>
</template>


<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling, messages } from '~/mixins/helper'
import Leaflet from '~/components/Leaflet.vue'
import MarqueeText from 'vue-marquee-text-component'
import ShiftStatusButton from '~/components/ShiftStatusButton.vue'

export default {
  name: 'ShiftCard',
  mixins: [helper, scheduling],
  components: {
    Leaflet,
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
    onlyinfo: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      locationOverlay: false,
    }
  },

  created () {
    // Checks for possible conflicts with user's existing shift assignements in all teams
    //this.checkShiftConflicts(this.shift, this.user_shifts)
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


    showLocationOverlay() {
      if (this.shift.location.map != null) {
        this.locationOverlay = true
      }
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
    font-size: 2.5rem;
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

  .dashed-border 
  {
    border-style: dashed;
    border-color: 'grey';
    border-width: thin;
  }

  .warning-text {
    font-size: .75rem;
    font-weight: bold;
    color: #ff0000;
    text-transform: uppercase;
  }

</style>