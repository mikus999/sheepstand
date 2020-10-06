<template>
  <v-container fluid>
    <v-data-table :headers="headersShift" :items="trades" dense width="100%">
      <template v-slot:item.day="{ item }">
        {{ item.time_start | formatDay }}
      </template>

      <template v-slot:item.shift_time="{ item }">
        {{ item.time_start | formatTime }} - {{ item.time_end | formatTime }}
      </template>

      <template v-slot:item.location="{ item }">
        <v-chip label small :color="item.location.color_code">{{ item.location.name }}</v-chip>
      </template>

      <template v-slot:item.tradewith="{ item }">
        <v-chip v-for="trade in item.trades" :key="trade.id" color="blue" label small @click="makeTrade(trade)" :disabled="trade.id === user.id" class="me-2">{{ trade.name }}</v-chip>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'TradeRequests',
  mixins: [helper],
  props: {},

  data () {
    return {
      trades: [],
      headersShift: [
        { text: this.$t('shifts.day'), value: 'day', align: 'left' },
        { text: this.$t('shifts.shift_time'), value: 'shift_time', align: 'left' },
        { text: this.$t('shifts.location'), value: 'location', align: 'left' },
        { text: this.$t('shifts.trade_requests'), value: 'tradewith', align: 'left', width: '50%' }
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
        })
      }
    }
  }
}
</script>