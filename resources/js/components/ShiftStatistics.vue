<template>
  <VueApexCharts :options="options" :series="series" height="500" width="500" />
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import VueApexCharts from 'vue-apexcharts'

export default {
  name: 'ShiftStatistics',
  mixins: [helper],
  components: {
    VueApexCharts
  },

  data () {
    return {
      stats: [],
      series: [],
      options: {
        chart: {
          height: 350,
          type: 'radialBar',
        },
        colors: ['#2196F3','#FF1744'],
        plotOptions: {
          radialBar: {
            hollow: {
              margin: 4,
              size: '40%',
              background: '#222222',
              image: undefined,
            },
            dataLabels: {
              name: {
                fontSize: '14px',
              },
              value: {
                fontSize: '14px',
                color: '#ffffff',
                formatter: (val) => {
                  return Math.round((val/100) * this.stats.total_spots)
                }
              },
              total: {
                show: true,
                label: 'Total Shifts',
                fontSize: '14px',
                color: '#ffffff',
                formatter: (val) => {
                  return this.stats.total_spots
                }
              }
            }
          }
        },
        labels: ['Available Spots','Trades'],
      }
    }
  },
  
  created () {
    this.getStats()
  },

  methods: {
    async getStats () {
      await axios.get('/api/teams/' + this.team.id + '/stats')
        .then(response => {
          this.stats = response.data.stats

          const avail_spots_pct = (this.stats.available_spots / this.stats.total_spots) * 100
          const avail_trades_pct = (this.stats.available_trades / this.stats.total_spots) * 100
          
          this.series.push(avail_spots_pct)
          this.series.push(avail_trades_pct)

        })
    },

  }
}
</script>