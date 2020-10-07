<template>
  <v-container fluid>
    <v-data-table :headers="headersShift" :items="trades" disable-sort width="100%">
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>mdi-account-convert</v-icon>
            {{ $t('shifts.trade_requests') }}
          </v-toolbar-title>
        </v-toolbar>
      </template>

      <template v-slot:item.view="{ item }">
        <v-icon small @click="showShiftOverlay(item)">mdi-magnify</v-icon>
      </template>

      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}
      </template>

      <template v-slot:item.shift_time="{ item }">
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
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
    </v-data-table>

    <v-overlay :value="shiftOverlay" @click.native="shiftOverlay = false">
      <ShiftCard :shift="shift" :schedule="schedule" onlyinfo width="300px"></ShiftCard>
    </v-overlay>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftCard from '~/components/ShiftCard.vue'

export default {
  name: 'TradeRequests',
  mixins: [helper],
  props: {},
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
        { text: '', value: 'view', align: 'left' },
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.shift_time'), value: 'shift_time', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('shifts.trade_requests'), value: 'tradewith', align: 'left' }
      ],
    }
  },

  created () {
    this.getTrades()
  },

  methods: {
    async getTrades () {
      await axios.get('/api/teams/' + this.team.id + '/trades')
        .then(response => {
          this.trades = response.data.trades
        })

    },

    async makeTrade (trade) {
      if (confirm(this.$t('shifts.confirm_trade'))) {
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