<template>
  <v-card width="100%">
    <v-toolbar flat>
      <v-toolbar-title>
        <v-icon left>{{ mdiAccountSwitch }}</v-icon>
        {{ $t('shifts.trade_requests') }}
      </v-toolbar-title>
            
      <template v-slot:extension>
        <v-switch v-model="allTeams" hide-details class="mx-4">
          <template v-slot:label>
            <span class="switch-label">{{ $t('shifts.show_all_teams') }}</span>
          </template>
        </v-switch>
      </template>
    </v-toolbar>


    <v-data-table 
      :headers="headersShift" 
      :items="filteredShifts || []" 
      disable-sort 
      width="100%"
      @click:row="showShiftOverlay"
    >



      <!-- MOBILE VIEW -->
      <template v-slot:body="{ items }" v-if="$vuetify.breakpoint.xs">
        <v-expansion-panels class="mt-3">
          <v-expansion-panel
            v-for="item in items"
            :key="item.id"
          >
            <v-expansion-panel-header class="py-0">
              <v-col cols="2" class="pa-0">
                <v-btn fab small class="location-avatar" :color="item.location.color_code" @click.stop="showLocationOverlay(item)">
                  {{ item.location.name.substring(0, 1) }}
                </v-btn>
              </v-col>

              <v-col class="shift-subtitle">
                <div class="text-h6 shift-title">{{ item.location.name }}</div>
                {{ item.time_start | formatDay }}<br>
                {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
              </v-col>
            </v-expansion-panel-header>

            <v-expansion-panel-content>
              <div class="shift-subtitle mb-2">
                <div class="text-h6 text-overline">{{ $t('shifts.trade_requests') }}</div>
                <div>{{ $t('shifts.trade_click_to_accept') }}</div>
              </div>

              <v-chip v-for="trade in item.trades" :key="trade.id" color="blue" label small @click="makeTrade(trade)" class="me-2"
                :disabled="trade.id === user.id" :outlined="trade.id === user.id">
                <v-icon left small>mdi-swap-horizontal-bold</v-icon>
                {{ trade.name }}
              </v-chip>

            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </template>




      <template v-slot:item.team_name="{ item }">
        {{ item.schedule.team.display_name }}
      </template>

      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}<br>
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
      </template>


      <template v-slot:item.location="{ item }">
        <v-btn fab x-small class="location-avatar-xs mr-2" :color="item.location.color_code" @click.stop="showLocationOverlay(item)">
          {{ item.location.name.substring(0, 1) }}
        </v-btn>
        <span>{{ item.location.name }}</span>
      </template>

      <template v-slot:header.tradewith="{ header }">
        {{ $t('shifts.trade_requests') }}<br>
        {{ $t('shifts.trade_click_to_accept') }}
      </template>

      <template v-slot:item.tradewith="{ item }">
        <v-chip v-for="trade in item.trades" :key="trade.id" color="blue" label small @click.stop="makeTrade(trade)" class="me-2"
           :disabled="trade.id === user.id" :outlined="trade.id === user.id">
           <v-icon left small>mdi-swap-horizontal-bold</v-icon>
           {{ trade.name }}
        </v-chip>
      </template>

    </v-data-table>

    <v-overlay :value="shiftOverlay" @click.native="shiftOverlay = false" :dark="theme=='dark'">
      <ShiftCard 
        :shift="shift" 
        onlyinfo 
        width="300px" 
        height="100%" 
        v-on:close="shiftOverlay = false"
        v-on:click.native.stop
      />
    </v-overlay>
    

    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
      <Leaflet 
        :location="location" 
        :width="mapWidth" 
        height="500px" 
        readonly 
        v-on:close="locationOverlay = false" 
        v-on:click.native.stop 
      />
    </v-overlay>

  </v-card>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling } from '~/mixins/helper'
import ShiftCard from '~/components/ShiftCard.vue'
import Leaflet from '~/components/Leaflet.vue'

export default {
  name: 'TradeRequests',
  mixins: [helper, scheduling],
  components: {
    ShiftCard,
    Leaflet
  },

  data () {
    return {
      shiftOverlay: false,
      locationOverlay: false,
      shift: null,
      location: null,
      allTeams: true,
      headersShift: [
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('teams.team_name'), value: 'team_name', align: 'left' },
        { text: this.$t('shifts.trade_requests'), value: 'tradewith', align: 'left' },
      ],
    }
  },

  computed: {
    ...mapGetters({
      trades: 'scheduling/trades'
    }),


    mapWidth() {
      var newWidth = this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
      return newWidth
    },


    filteredShifts() {
      if (this.allTeams) {
          return this.trades
      } else {
          return this.trades.filter(shift => shift.schedule.team_id === this.team.id)
      }
    }
  },

  created () {
    this.getTrades()
  },

  methods: {
    async getTrades () {
      await axios.get('/api/schedules/trades')
        .then(response => {
          this.storeTrades(response.data.data.trades)
        })
    },

    async makeTrade (trade) {
      if (await this.$root.$confirm(this.$t('shifts.confirm_accept'), null, 'primary')) {
        await axios({
          method: 'post',      
          url: '/api/schedules/trades',
          data: {
            team_id: this.team.id,
            shift_id: trade.pivot.shift_id,
            user_id: trade.pivot.user_id
          }
        })
        .then(response => {
          this.storeTrades(response.data.data.trades)
          this.storeUserShifts(response.data.data.usershifts)
          this.showSnackbar(this.$t('shifts.success_trade_made'), 'success')
        })
      }
    },

    showShiftOverlay(shift) {
      this.shift = shift
      this.shiftOverlay = true
    },
    
    showLocationOverlay(shift) {
      this.location = shift.location
      this.locationOverlay = true
    },


    filterShiftUsers(shiftUsers) {
      return shiftUsers.filter(u => u.pivot.status != 3)
    },

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
    font-size: 1.0rem !important;
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
</style>