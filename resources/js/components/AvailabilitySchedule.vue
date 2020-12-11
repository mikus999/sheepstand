<template>
  <v-card width="100%" :flat="$vuetify.breakpoint.xs">
    <v-card-title class="text-h6">
      <v-icon class="mr-3">mdi-calendar-multiselect</v-icon>
      {{ $t('account.availability') }}
    </v-card-title>

    <!-- Desktop View -->
    <v-card-text v-if="$vuetify.breakpoint.smAndUp">
      <v-row>
        <v-col>
          <v-btn text @click="changeAll(1)" color="grey">
            {{ $t('general.enable_all') }}
          </v-btn>
          <v-btn text @click="changeAll(0)" color="grey">
            {{ $t('general.disable_all') }}
          </v-btn>
          <v-btn @click="saveSchedule" color="primary">
            {{ $t('general.save') }}
          </v-btn>
        </v-col>
      </v-row>

      <v-row>
        <v-col>
          <div class="avail_col">
            <p>{{ $t('account.time_slot') }}</p>
            <div v-for="h in 24" :key="'row_' + h" class="avail_row">
              {{ timeSlot(h) }}
            </div>
          </div>

          <div v-for="d in 7" :key="'day_' + (d)" class="avail_col text-center">
            <p>{{ dayOfWeek(d) }}</p>

            <div 
              v-for="(h, index) in filterAvailability(d)" 
              :key="index" 
              :class="'avail_square ' + (h.available ? 'avail_square_on' : 'avail_square_off')"
              @click="changeAvailability(h)"
            >
              <v-icon>{{ h.available ? 'mdi-check-circle-outline' : 'mdi-cancel' }}</v-icon>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-card-text>


    <!-- Mobile View -->
    <v-card-text v-else>
      <v-row>
        <v-col>
          <v-btn block @click="saveSchedule" color="primary">
            {{ $t('general.save') }}
          </v-btn>
          <v-btn text block @click="changeAll(1)" color="grey">
            {{ $t('general.enable_all') }}
          </v-btn>
          <v-btn text block @click="changeAll(0)" color="grey">
            {{ $t('general.disable_all') }}
          </v-btn>
        </v-col>
      </v-row>

      <v-tabs vertical>
        <v-tab v-for="d in 7" :key="d">
          {{ dayOfWeek(d) }}
        </v-tab>

        <v-tab-item v-for="d in 7" :key="d">
          <v-btn 
            v-for="(h, index) in filterAvailability(d)" 
            :key="index" 
            block small
            @click="changeAvailability(h)"
            class="my-2"
            :color="h.available ? 'green' : '#AEAEAE'"
          >
            <v-icon class="mr-auto" small>{{ h.available ? 'mdi-check-circle-outline' : 'mdi-cancel' }}</v-icon>
            <span class="mx-auto">{{ timeSlot(index + 1) }}</span>
          </v-btn>
        </v-tab-item>


      </v-tabs>
          
    </v-card-text>

  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: "AvailabilitySchedule",
  mixins: [helper],

  props: {
    data: {
      type: [Object, Array]
    }
  },

  data() {
    return {
      tabs: 1,
      availability: [],
      changed: []
    }
  },


  created() {
    this.getAvailability()
  },

  methods: {
    async getAvailability () {
      await axios.get('/api/account/availability')
        .then(response => {
          this.availability = response.data
        })
    },

    filterAvailability(day) {
      var dayAvail = this.availability.filter(d => d.day_of_week == day)
      return dayAvail
    },

    changeAvailability(hour) {
      var index = this.availability.indexOf(hour)
      var inChanged = this.changed.indexOf(hour)

      // First, change the master array
      this.availability[index].available = !hour.available

      // Then, either add or remove it from the changed items array
      if (inChanged > -1) {
        this.changed.splice(inChanged, 1)
      } else {
        this.changed.push(hour)
      }

    },

    async changeAll(turnOn) {
      await axios({
        method: 'post',      
        url: '/api/account/availability/default',
        data: {
          default: turnOn
        }
      })
      .then(response => {
        this.availability = response.data
        this.changed = []
        this.showSnackbar(this.$t('general.info_updated'), 'success')
        this.refreshStore()
        this.$emit('updated')
      })
    },

    async saveSchedule() {
      if (this.changed.length > 0) {
        await axios({
          method: 'post',      
          url: '/api/account/availability',
          data: {
            availability: JSON.stringify(this.changed)
          }
        })
        .then(response => {
          this.changed = []
          this.showSnackbar(this.$t('general.info_updated'), 'success')
          this.refreshStore()
        })
      }
    },

    dayOfWeek (daynum) {
      var days = []
      days = this.$dayjs().localeData().weekdaysShort()
      days[7] = days[0]
      return days[daynum]
    },

    timeSlot(hour) {
      const startHour = this.$dayjs().hour(hour-1).minute(0).format('LT')
      const endHour = this.$dayjs().hour(hour).minute(0).format('LT')

      return startHour + ' - ' + endHour
    }
  },
}
</script>

<style scoped>
  .avail_row {
    height: 35px;
    margin: 4px;
    line-height: 35px;
  }

  .avail_col {
    display: inline-block;
  }

  .avail_square {
    width: 35px;
    height: 35px;
    margin: 4px;
    padding: 0px;
    line-height: 35px;
    border-radius: 1.5px;
  }

  .avail_square_off {
    background-color: #AEAEAE;
  }

  .avail_square_on {
    background-color: #4CAF50;
  }

</style>