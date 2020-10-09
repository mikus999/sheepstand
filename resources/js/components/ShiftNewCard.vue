<template>
  <v-card>
    <v-card-title class="text-center">
      <span class="headline">{{ $t('schedules.new_shift') }} - {{ shift.time_start | formatDate }}</span>
    </v-card-title>

    <v-card-text>
      <v-container>
        <v-row>
          <v-col cols=2><v-icon>mdi-map-marker</v-icon></v-col>
          <v-col cols=10>
            <v-select v-model="shift.location_id" :items="locations" item-value="id" item-text="name" outlined dense />
          </v-col>
        </v-row>
        
        <v-row class="mt-5">
          <v-col cols=2><v-icon>mdi-clock</v-icon></v-col>
          <v-col cols=5>
            <v-dialog ref="dialog1" v-model="time.start.show" :return-value.sync="time.start.value" persistent width="290px">
              <template v-slot:activator="{ on, attrs }">
                <v-text-field v-model="shift.time_start" outlined readonly dense v-bind="attrs" v-on="on"></v-text-field>
              </template>

              <v-time-picker v-if="time.start" v-model="shift.time_start" :format="timeFormat" full-width :allowed-minutes="allowedStep">
                <v-spacer></v-spacer>
                <v-btn text color="primary" @click="time.start.show = false">{{ $t('general.cancel')}}</v-btn>
                <v-btn text color="primary" @click="$refs.dialog1.save(time.start.value)">{{ $t('general.ok')}}</v-btn>
              </v-time-picker>
            </v-dialog>
          </v-col>
          <v-col cols=5>
            <v-dialog ref="dialog2" v-model="time.end.show" :return-value.sync="time.end.value" persistent width="290px">
              <template v-slot:activator="{ on, attrs }">
                <v-text-field v-model="shift.time_end" outlined readonly dense v-bind="attrs" v-on="on"></v-text-field>
              </template>

              <v-time-picker v-if="time.end" v-model="shift.time_end" :format="timeFormat" full-width :allowed-minutes="allowedStep">
                <v-spacer></v-spacer>
                <v-btn text color="primary" @click="time.end.show = false">{{ $t('general.cancel')}}</v-btn>
                <v-btn text color="primary" @click="$refs.dialog2.save(time.start.value)">{{ $t('general.ok')}}</v-btn>
              </v-time-picker>
            </v-dialog>
          </v-col>
        </v-row>

        <v-row class="mt-10">
          <v-col cols=2><v-icon>mdi-account-tie</v-icon></v-col>
          <v-col cols=10>
            <v-range-slider v-model="participants" :thumb-size="16" thumb-label="always"
              min="1" max="8" ticks="always" tick-size="4" />
          </v-col>
        </v-row>
      </v-container>
    </v-card-text>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="secondary" text @click="close">{{ $t('general.close') }}</v-btn>
      <v-btn color="secondary" text @click="addShift">
        {{ edit ? $t('general.save') : $t('general.create') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftNewCard from '~/components/ShiftNewCard.vue'

export default {
  name: 'ShiftEditCard',
  mixins: [helper],
  components: {
    ShiftNewCard
  },
  props: {
    shift: {
      type: Object
    },
    schedule: {
      type: Object
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '170px'
    },
    edit: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      locations: [],
      time: {
        start: {
          value: '',
          show: false
        },
        end: {
          value: '',
          show: false
        }
      },
      participants: []
    }
  },

    
  computed: {    
    timeFormat () {
      const localeTime = this.$dayjs().localeData().longDateFormat('LT')
      const isAmPm = localeTime.indexOf('A') >= 0
      return (isAmPm ? 'ampm' : '24hr')
    }
  },
  
  created () {
    this.getLocations()
    this.participants = [this.shift.min_participants, this.shift.max_participants]
  },

  methods: {

    async getLocations () {
      await axios.get('/api/teams/' + this.team.id + '/locations')
        .then(response => {
          this.locations = response.data
        })
    },

    close () {
      this.dialog = false
    },


    addShift () {
      var tempStart = this.$dayjs(this.shiftData.date + ' ' + this.shiftData.start).format('YYYY-MM-DD HH:mm:ss')
      var tempEnd = this.$dayjs(this.shiftData.date + ' ' + this.shiftData.end).format('YYYY-MM-DD HH:mm:ss')

      if (!this.$dayjs(tempStart).isBefore(this.$dayjs(tempEnd))) {
        tempEnd = this.$dayjs(tempStart).add(2, 'h').format('YYYY-MM-DD HH:mm:ss')
      }

      if (!this.shiftData.edit) {
        const formData = new FormData()
        formData.append('location_id', this.shiftData.location)
        formData.append('time_start', tempStart)
        formData.append('time_end', tempEnd)
        formData.append('min_participants', this.shiftData.participants[0])
        formData.append('max_participants', this.shiftData.participants[1])

        axios.post('/api/schedules/' + this.id + '/shifts', formData)
          .then(response => {
            this.getShiftData(this.date)
            this.close()  
          })

      } else {
        var newShift = []
        newShift.id = this.shiftData.id
        newShift.location_id = this.shiftData.location
        newShift.time_start = tempStart
        newShift.time_end = tempEnd
        newShift.min_participants = this.shiftData.participants[0]
        newShift.max_participants = this.shiftData.participants[1]

        this.updateShift(newShift)
      }

    },


    async updateShift (data) {
      await axios({
        method: 'patch',      
        url: '/api/schedules/' + this.id + '/shifts/' + data.id,
        data: {
          location_id: data.location_id,
          time_start: data.time_start,
          time_end: data.time_end,
          min_participants: data.min_participants,
          max_participants: data.max_participants
        }
      })
      .then(response => {
        this.getShiftData(this.date)
        this.close()  
      })
    },

    allowedStep: m => m % 15 === 0,

  }
}
</script>