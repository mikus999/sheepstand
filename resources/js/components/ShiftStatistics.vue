<template>
  <v-card>
      <v-toolbar flat>
        <v-toolbar-title>
          <v-icon left>{{ icons.mdiGauge }}</v-icon>
          {{ $t('general.statistics') }}
        </v-toolbar-title>
      </v-toolbar>

    <v-row>
      <v-col sm=6 lg=12 class="text-center">
        <VueApexCharts :options="optionsPlaces" :series="seriesPlaces" width="100%" />
      </v-col>

      <v-col sm=6 lg=12 class="text-center">
        <VueApexCharts :options="optionsShifts" :series="seriesShifts" width="100%" />
      </v-col>
    </v-row>
  </v-card>
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
      seriesPlaces: [],
      optionsPlaces: {
        chart: {
          height: 350,
          type: 'radialBar',
        },
        labels: ['Available Spots','Trades'],
        colors: ['#2196F3','#00BCD4'],
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'dark',
            type: 'vertical',
            shadeIntensity: 0.5,
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
          }
        },
        stroke: {
          lineCap: 'round'
        },
        plotOptions: {
          radialBar: {
            hollow: {
              margin: 0,
              size: '40%',
              background: '#222222',
              image: undefined,
              position: 'front',
              dropShadow: {
                enabled: true,
                top: 0,
                left: 0,
                blur: 4,
                opacity: 0.24
              }
            },
            track: {
              background: ['#BBDEFB','#E0F7FA']
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
                label: 'Total Spots',
                fontSize: '14px',
                color: '#ffffff',
                formatter: (val) => {
                  return this.stats.total_spots
                }
              }
            }
          }
        },
        stroke: {
          lineCap: 'round'
        },
      },

      seriesShifts: [],
      optionsShifts: {
        chart: {
          height: 350,
          type: 'radialBar',
        },
        labels: ['Shift with Needs','Shifts with Trades'],
        colors: ['#2196F3','#00BCD4'],
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'dark',
            type: 'vertical',
            shadeIntensity: 0.5,
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
          }
        },
        stroke: {
          lineCap: 'round'
        },
        plotOptions: {
          radialBar: {
            hollow: {
              margin: 0,
              size: '40%',
              background: '#222222',
              image: undefined,
              position: 'front',
              dropShadow: {
                enabled: true,
                top: 0,
                left: 0,
                blur: 4,
                opacity: 0.24
              }
            },
            track: {
              background: ['#BBDEFB','#E0F7FA']
            },
            dataLabels: {
              name: {
                fontSize: '14px',
              },
              value: {
                fontSize: '14px',
                color: '#ffffff',
                formatter: (val) => {
                  return Math.round((val/100) * this.stats.total_shifts)
                }
              },
              total: {
                show: true,
                label: 'Total Shifts',
                fontSize: '14px',
                color: '#ffffff',
                formatter: (val) => {
                  return this.stats.total_shifts
                }
              }
            },
          },
        }
      }
    }
  },
  
  created () {
    if (this.team) {
      this.getStats()
    } else {
      const unsubscribe = this.$store.subscribe((mutation, state) => {
        if(mutation.type === 'auth/SET_TEAM') {
          this.getStats()
          unsubscribe()
        }
      })
    }
  },

  methods: {
    async getStats () {
      await axios.get('/api/teams/' + this.team.id + '/stats')
        .then(response => {
          this.stats = response.data.data.stats

          const avail_spots_pct = (this.stats.available_spots / this.stats.total_spots) * 100
          const avail_trades_pct = (this.stats.available_trades / this.stats.total_spots) * 100
          this.seriesPlaces.push(avail_spots_pct)
          this.seriesPlaces.push(avail_trades_pct)

          const shifts_with_needs = (this.stats.shifts_with_needs / this.stats.total_shifts) * 100
          const shifts_with_trades = (this.stats.shifts_with_trades / this.stats.total_shifts) * 100
          this.seriesShifts.push(shifts_with_needs)
          this.seriesShifts.push(shifts_with_trades)
        })
    },

  }
}
</script>