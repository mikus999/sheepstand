<template>
  <v-card>
    <v-card-title class="justify-center">
      <span class="headline">{{ edit ? $t('schedules.change_shift') : $t('schedules.new_shift') }}</span>
    </v-card-title>

    <v-card-text>
      <v-container>
        <v-row>
          <v-col>
            <!-- Location Picker -->
            <v-select 
              v-model="shift.location_id" 
              :items="locations" 
              item-value="id" 
              item-text="name" 
              outlined 
              dense
              hide-details
              prepend-icon="mdi-map-marker"
              :label="$t('shifts.location')"
            />
          </v-col>
        </v-row>

        <v-row>
          <v-col>

            <!-- Shift Date Picker (SCHEDULES ONLY) -->
            <v-menu 
              v-if="!isTemplate"
              ref="menu" 
              v-model="date_menu" 
              :close-on-content-click="false" 
              :return-value.sync="shift_date"
              transition="scale-transition" 
              offset-y 
              min-width="290px"
            >

              <template v-slot:activator="{ on, attrs }">
                <v-text-field 
                  v-model="shift_date" 
                  :label="$t('shifts.date')" 
                  prepend-icon="mdi-calendar" 
                  readonly
                  v-bind="attrs" 
                  v-on="on"
                >
                </v-text-field>
              </template>

              <v-date-picker 
                v-model="shift_date" 
                no-title 
                scrollable 
                :locale="locale"
                :first-day-of-week="$dayjs().localeData().firstDayOfWeek()"
              >
                <v-spacer></v-spacer>
                <v-btn text color="secondary" @click="date_menu = false">{{ $t('general.cancel') }}</v-btn>
                <v-btn color="primary" @click="$refs.menu.save(shift_date)">{{ $t('general.ok') }}</v-btn>
              </v-date-picker>

            </v-menu>


            <!-- Day picker (TEMPLATES ONLY) -->
            <v-select
              v-else
              v-model="shift_date"
              :items="template_days" 
              item-value="date" 
              item-text="date" 
              :label="$t('shifts.day')"
              prepend-icon="mdi-calendar"
              hide-details
            >
              <template v-slot:selection="{ item }">
                {{ $dayjs(item.date).format('dddd') }}
              </template>

              <template v-slot:item="{ item }">
                {{ $dayjs(item.date).format('dddd') }}
              </template>
            </v-select>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols=6>
            <!-- Start Time Picker -->
            <v-dialog 
              ref="dialog1" 
              v-model="time.start.show" 
              persistent 
              width="290px"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field 
                  v-model="time.start.value" 
                  outlined 
                  readonly 
                  dense 
                  hide-details
                  :label="$t('shifts.time_start')"
                  v-bind="attrs" 
                  v-on="on" 
                  prepend-icon="mdi-clock"
                >
                </v-text-field>
              </template>

              <v-time-picker v-if="time.start.show" v-model="time.start.value" :format="timeFormat" full-width :allowed-minutes="allowedStep">
                <v-spacer></v-spacer>
                <v-btn text color="secondary" @click="time.start.show = false">{{ $t('general.cancel')}}</v-btn>
                <v-btn color="primary" @click="$refs.dialog1.save(time.start.value)">{{ $t('general.ok')}}</v-btn>
              </v-time-picker>
            </v-dialog>
          </v-col>
          <v-col cols=6>
            <!-- End Time Picker -->
            <v-dialog 
              ref="dialog2" 
              v-model="time.end.show" 
              persistent 
              width="290px"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field 
                  v-model="time.end.value" 
                  outlined 
                  readonly 
                  dense 
                  hide-details
                  :label="$t('shifts.time_end')"
                  v-bind="attrs" 
                  v-on="on"
                ></v-text-field>
              </template>

              <v-time-picker v-if="time.end.show" v-model="time.end.value" :format="timeFormat" full-width :allowed-minutes="allowedStep">
                <v-spacer></v-spacer>
                <v-btn text color="secondary" @click="time.end.show = false">{{ $t('general.cancel')}}</v-btn>
                <v-btn color="primary" @click="$refs.dialog2.save(time.start.value)">{{ $t('general.ok')}}</v-btn>
              </v-time-picker>
            </v-dialog>
          </v-col>
        </v-row>

        <v-row class="mt-8">
          <v-col>
            <!-- Number of Participants slider -->
            <v-range-slider 
              v-model="participants" 
              :thumb-size="16" 
              thumb-label="always"
              min="1" 
              max="8" 
              ticks="always" 
              tick-size="4" 
              prepend-icon="mdi-account-tie"
              persistent-hint
              :hint="$t('shifts.number_of_participants')"
            />
          </v-col>
        </v-row>


        <v-row>
          <v-col>
            <v-switch
              v-model="mandatory"
              :label="$t('shifts.mandatory')"
              :hint="$t('shifts.mandatory_explanation')"
              persistent-hint
              prepend-icon="mdi-heart"
            />
          </v-col>
        </v-row>        
      </v-container>
    </v-card-text>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="secondary" text @click="close">{{ $t('general.close') }}</v-btn>
      <v-btn color="primary" @click="addShift">
        {{ edit ? $t('general.save') : $t('general.create') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling } from '~/mixins/helper'
import ShiftNewCard from '~/components/ShiftNewCard.vue'

export default {
  name: 'ShiftNewCard',
  mixins: [helper, scheduling],
  components: {
    ShiftNewCard
  },
  props: {
    shift: {
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
      template_days: [],
      shift_date: null,
      date_menu: false,
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
      participants: [],
      mandatory: false,
    }
  },

    
  computed: {    
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
    }),

    timeFormat () {
      const localeTime = this.$dayjs().localeData().longDateFormat('LT')
      const isAmPm = localeTime.indexOf('A') >= 0
      return (isAmPm ? 'ampm' : '24hr')
    },

    isTemplate () {
      return this.schedule.status == 9
    }
  },
  
  created () {
    this.getLocations()
    this.getTemplateDays()
    this.shift_date = this.$dayjs(this.shift.time_start).format('YYYY-MM-DD')
    this.time.start.value = this.$dayjs(this.shift.time_start).format('HH:mm')
    this.time.end.value = this.$dayjs(this.shift.time_end).format('HH:mm')
    this.participants = [this.shift.min_participants, this.shift.max_participants]
    this.mandatory = this.shift.mandatory
  },

  methods: {

    async getLocations () {
      await axios.get('/api/teams/' + this.team.id + '/locations')
        .then(response => {
          this.locations = response.data.data.locations

          if (!this.edit) {
            this.shift.location_id = this.locations[0].id
          }
        })
    },

    getTemplateDays () {
      var date_start = this.schedule.date_start
      for (var i = 0; i < 7; i++) {
        var temp_array = {
          id: i, 
          date: this.$dayjs(date_start).add(i, 'd').format('YYYY-MM-DD')
        }

        this.template_days.push(temp_array)
      }
    },

    close () {
      this.$emit('close')
    },


    async addShift () {
      var tempStart = this.$dayjs(this.shift_date).format('YYYY-MM-DD') + ' ' + this.time.start.value
      var tempEnd = this.$dayjs(this.shift_date).format('YYYY-MM-DD') + ' ' + this.time.end.value

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
          max_participants: this.participants[1],
          mandatory: this.mandatory
        }
      })
      .then(response => {
        this.storeSchedule(response.data.data.schedule)
        this.$emit('update')
        this.close()  
      })

    },


    allowedStep: m => m % 15 === 0,

  }
}
</script>