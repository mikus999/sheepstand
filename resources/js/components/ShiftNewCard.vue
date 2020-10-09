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
            <v-dialog ref="dialog1" v-model="time.start.show" persistent width="290px">
              <template v-slot:activator="{ on, attrs }">
                <v-text-field v-model="time.start.value" outlined readonly dense v-bind="attrs" v-on="on"></v-text-field>
              </template>

              <v-time-picker v-if="time.start.show" v-model="time.start.value" :format="timeFormat" full-width :allowed-minutes="allowedStep">
                <v-spacer></v-spacer>
                <v-btn text color="primary" @click="time.start.show = false">{{ $t('general.cancel')}}</v-btn>
                <v-btn text color="primary" @click="$refs.dialog1.save(time.start.value)">{{ $t('general.ok')}}</v-btn>
              </v-time-picker>
            </v-dialog>
          </v-col>
          <v-col cols=5>
            <v-dialog ref="dialog2" v-model="time.end.show" persistent width="290px">
              <template v-slot:activator="{ on, attrs }">
                <v-text-field v-model="time.end.value" outlined readonly dense v-bind="attrs" v-on="on"></v-text-field>
              </template>

              <v-time-picker v-if="time.end.show" v-model="time.end.value" :format="timeFormat" full-width :allowed-minutes="allowedStep">
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
    this.time.start.value = this.$dayjs(this.shift.time_start).format('HH:mm')
    this.time.end.value = this.$dayjs(this.shift.time_end).format('HH:mm')
    this.participants = [this.shift.min_participants, this.shift.max_participants]
  },

  methods: {

    async getLocations () {
      await axios.get('/api/teams/' + this.team.id + '/locations')
        .then(response => {
          this.locations = response.data

          if (!this.edit) {
            this.shift.location_id = this.locations[0].id
          }
        })
    },

    close () {
      this.$emit('close')
    },


    async addShift () {
      var tempStart = this.$dayjs(this.shift.time_start).format('L') + ' ' + this.time.start.value
      var tempEnd = this.$dayjs(this.shift.time_end).format('L') + ' ' + this.time.end.value

      if (!this.$dayjs(tempStart).isBefore(this.$dayjs(tempEnd))) {
        tempEnd = this.$dayjs(tempStart).add(this.team.default_shift_minutes, 'm')
      }

      tempStart = this.$dayjs(tempStart).format('YYYY-MM-DD HH:mm:ss')
      tempEnd = this.$dayjs(tempEnd).format('YYYY-MM-DD HH:mm:ss')


      if (this.edit) {
        var method = 'PATCH'
        var url = '/api/schedules/' + this.schedule.id + '/shifts/' + this.shift.id
      } else {
        var method = 'POST'
        var url = '/api/schedules/' + this.schedule.id + '/shifts'
      }


      await axios({
        method: method,      
        url: url,
        data: {
          location_id: this.shift.location_id,
          time_start: tempStart,
          time_end: tempEnd,
          min_participants: this.participants[0],
          max_participants: this.participants[1]
        }
      })
      .then(response => {
        this.$emit('update', response.data.schedule)
        this.close()  
      })

    },


    allowedStep: m => m % 15 === 0,

  }
}
</script>