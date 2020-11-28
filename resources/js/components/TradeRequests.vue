<template>
  <v-card>
    <v-data-table :headers="headersShift" :items="trades" disable-sort width="100%">
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>mdi-account-convert</v-icon>
            {{ $t('shifts.trade_requests') }}
          </v-toolbar-title>
        </v-toolbar>
      </template>


      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}<br>
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
      </template>

      <template v-slot:item.shift_time="{ item }">
        
      </template>

      <template v-slot:item.location="{ item }">
        <v-chip label small :color="item.location.color_code">{{ item.location.name }}</v-chip>
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

      <template v-slot:item.view="{ item }">
        <v-btn icon @click="showShiftOverlay(item)">
          <v-icon>mdi-card-account-details-outline</v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <v-overlay :value="shiftOverlay" :dark="theme=='dark'">
      <ShiftCard :shift="shift" :schedule="schedule" onlyinfo width="300px" height="100%" v-on:close="shiftOverlay = false"></ShiftCard>
    </v-overlay>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftCard from '~/components/ShiftCard.vue'

export default {
  name: 'TradeRequests',
  mixins: [helper],
  components: {
    ShiftCard
  },

  data () {
    return {
      shiftOverlay: false,
      shift: null,
      schedule: null,
      trades: [],
      headersShift: [
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('shifts.trade_requests'), value: 'tradewith', align: 'left' },
        { text: '', value: 'view', align: 'left' }
      ],
    }
  },

  created () {
    if (this.team) {
      this.getTrades()
    } else {
      const unsubscribe = this.$store.subscribe((mutation, state) => {
        if(mutation.type === 'auth/SET_TEAM') {
          this.getTrades()
          unsubscribe()
        }
      })
    }
  },

  methods: {
    async getTrades () {
      await axios.get('/api/teams/' + this.team.id + '/trades')
        .then(response => {
          this.trades = response.data.trades
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
          this.trades = response.data.trades.trades
          this.showSnackbar(this.$t('shifts.success_trade_made'), 'success')
        })
      }
    },

    showShiftOverlay(shift) {
      this.shift = shift
      this.schedule = shift.schedule
      this.shiftOverlay = true
    }
  }
}
</script>