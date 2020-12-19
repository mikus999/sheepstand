<template>
  <v-card>
    <v-data-table :headers="headersShift" :items="trades || []" disable-sort width="100%">
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>mdi-account-switch</v-icon>
            {{ $t('shifts.trade_requests') }}
          </v-toolbar-title>
        </v-toolbar>
      </template>


      <template v-slot:item.team_name="{ item }">
        <v-btn icon @click="showShiftOverlay(item)">
          <v-icon>mdi-card-account-details-outline</v-icon>
        </v-btn>
        {{ item.schedule.team.display_name }}
      </template>

      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}<br>
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
      </template>


      <template v-slot:item.location="{ item }">
        <v-chip label small :color="item.location.color_code" @click="showLocationOverlay(item)">{{ item.location.name }}</v-chip>
      </template>

      <template v-slot:header.tradewith="{ header }">
        {{ $t('shifts.trade_requests') }}<br>
        {{ $t('shifts.trade_click_to_accept') }}
      </template>

      <template v-slot:item.tradewith="{ item }">
        <v-chip v-for="trade in item.trades" :key="trade.id" color="blue" label small @click="makeTrade(trade)" class="me-2"
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
      headersShift: [
        { text: this.$t('teams.team_name'), value: 'team_name', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
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

  },

  created () {
    this.getTrades()
  },

  methods: {
    async getTrades () {
      await axios.get('/api/teams/' + this.team.id + '/trades')
        .then(response => {
          this.storeTrades(response.data.trades)
        })
    },

    async makeTrade (trade) {
      if (await this.$root.$confirm(this.$t('shifts.confirm_trade'), null, 'primary')) {
        await axios({
          method: 'post',      
          url: '/api/teams/' + this.team.id + '/trades',
          data: {
            shift_id: trade.pivot.shift_id,
            user_id: trade.pivot.user_id
          }
        })
        .then(response => {
          this.storeTrades(response.data.trades)
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

  }
}
</script>