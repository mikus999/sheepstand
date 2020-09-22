<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('schedules.schedule')"></PageTitle>
    </v-row>

    <v-row>
      <v-col xs=1 sm=4 class="text-left" >
        <v-btn text class="mr-auto" :x-large="$vuetify.breakpoint.smAndUp" @click="$router.go(-1)">
          <v-icon left>mdi-arrow-left</v-icon>
          <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('general.go_back')}}</span>
        </v-btn>
      </v-col>

      <v-col xs=10 sm=4 class="text-center">
        <span class="text-h6 mx-auto">{{ $t('schedules.week_of')}} {{ schedData.date_start | formatDate }}</span>
      </v-col>
      
      <v-col xs=1 sm=4 class="text-right">
        <v-btn text class="ml-auto" :x-large="$vuetify.breakpoint.smAndUp" @click="editAssignments" v-show="schedData.status > 0">
          <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('schedules.assignments') }}</span>
          <v-icon right>mdi-arrow-right</v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <v-row class="mt-5 mb-5">
      <div class="swiper-button-prev" v-if="$vuetify.breakpoint.smAndUp"></div>
      <div class="swiper-button-next" v-if="$vuetify.breakpoint.smAndUp"></div>      

      <v-slider v-model="schedData.status" min="0" max="2" :tick-labels="tickLabels" :color="scheduleStatus[schedData.status].color"
        ticks="always" tick-size="4" @click="updateScheduleStatus" class="mb-8">
      </v-slider>
    </v-row>

    <v-row>
      <swiper ref="mySwiper" :options="swiperOptions">
        <swiper-slide v-for="day in days7" :key="day.id" class="text-center">
          <h4>{{ day.date | formatWeekdayShort }}</h4>
          <h6>{{ day.date | formatDate }}</h6>

          <draggable class="list-group" tag="transition-group" v-model="day.list" v-bind="dragOptions" 
            @end="moveShift" draggable=".shift" :id="day.id" handle=".handle">

              <!-- SHIFT CARDS -->
              <v-card v-for="shift in day.list" :key="shift.id" :id="shift.id" class="shift mt-5 handle" :color="shift.location.color_code">
                <v-card-text class="shift-body text-center pa-0">
                  <v-row dense>
                    <v-col cols=2><v-icon small>mdi-map-marker</v-icon></v-col>
                    <v-col cols=8 class="font-weight-bold">{{ shift.location.name }}</v-col>
                  </v-row>
                  <v-row dense>
                    <v-col cols=2><v-icon small>mdi-clock</v-icon></v-col>
                    <v-col cols=8 class="font-weight-bold">{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</v-col>
                  </v-row>
                  <v-row dense>
                    <v-col cols=2><v-icon small>mdi-account-tie</v-icon></v-col>
                    <v-col cols=3 offset="1">
                      <v-chip x-small>{{ shift.min_participants }}</v-chip><br>
                      <span>{{ $t('general.min') }}</span>
                    </v-col>
                    <v-col cols=2>
                      <v-chip x-small>{{ shift.max_participants }}</v-chip><br>
                      <span>{{ $t('general.max') }}</span>
                    </v-col>
                  </v-row>
                </v-card-text>
                
                <v-divider class="pa-0 ma-0" />

                <v-card-actions class="pa-0">
                  <v-row dense>
                    <v-col>
                      <v-btn icon @click="showShiftDialog(shift, true)">
                        <v-icon small>mdi-pencil</v-icon>
                      </v-btn>
                    </v-col>
                    <v-col>
                      <v-btn icon @click="deleteShift(shift.id)">
                        <v-icon small>mdi-delete</v-icon>
                      </v-btn>
                    </v-col>
                    <v-col>
                      <v-btn icon @click="duplicateShift(shift.id)">
                        <v-icon small>mdi-content-duplicate</v-icon>
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-card-actions>
              </v-card>

              <v-card slot="footer" v-if="day.list.length === 0" class="no-shift d-flex align-center mt-5" :key="day.id">
                <v-card-text class="text-center pa-0">
                  <v-icon large class="pa-4">mdi-select-place</v-icon>
                </v-card-text>
              </v-card>

              <v-card slot="header" class="mt-5 text-center" key="footer" @click.stop="showShiftDialog(day, false)">
                <v-card-text class="text-center pa-0">
                  <v-icon large class="pa-4">mdi-plus-box</v-icon>
                </v-card-text>
              </v-card>
          </draggable>
        </swiper-slide>
      </swiper>
    </v-row>

    <!-- NEW/EDIT SHIFT DIALOG -->
    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title class="text-center">
          <span class="headline">{{ $t('schedules.new_shift') }} - {{ shiftData.date | formatDate }}</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols=2><v-icon>mdi-map-marker</v-icon></v-col>
              <v-col cols=10>
                <v-select v-model="shiftData.location" :items="locations" item-value="id" item-text="name" outlined dense />
              </v-col>
            </v-row>
            
            <v-row class="mt-5">
              <v-col cols=2><v-icon>mdi-clock</v-icon></v-col>
              <v-col cols=5>
                <v-dialog ref="dialog1" v-model="time.start" :return-value.sync="shiftData.start" persistent width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field v-model="shiftData.start" outlined readonly dense v-bind="attrs" v-on="on"></v-text-field>
                  </template>

                  <v-time-picker v-if="time.start" v-model="shiftData.start" :format="timeFormat" full-width :allowed-minutes="allowedStep">
                    <v-spacer></v-spacer>
                    <v-btn text color="primary" @click="time.start = false">{{ $t('general.cancel')}}</v-btn>
                    <v-btn text color="primary" @click="$refs.dialog1.save(shiftData.start)">{{ $t('general.ok')}}</v-btn>
                  </v-time-picker>
                </v-dialog>
              </v-col>
              <v-col cols=5>
                <v-dialog ref="dialog2" v-model="time.end" :return-value.sync="shiftData.end" persistent width="290px">
      >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field v-model="shiftData.end" outlined readonly dense v-bind="attrs" v-on="on"></v-text-field>
                  </template>

                  <v-time-picker v-if="time.end" v-model="shiftData.end" :format="timeFormat" full-width :allowed-minutes="allowedStep">
                    <v-spacer></v-spacer>
                    <v-btn text color="primary" @click="time.end = false">{{ $t('general.cancel')}}</v-btn>
                    <v-btn text color="primary" @click="$refs.dialog2.save(shiftData.end)">{{ $t('general.ok')}}</v-btn>
                  </v-time-picker>
                </v-dialog>
              </v-col>
            </v-row>

            <v-row class="mt-10">
              <v-col cols=2><v-icon>mdi-account-tie</v-icon></v-col>
              <v-col cols=10>
                <v-range-slider v-model="shiftData.participants" :thumb-size="16" thumb-label="always"
                  min="1" max="8" ticks="always" tick-size="4" />
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" text @click="close">{{ $t('general.close') }}</v-btn>
          <v-btn color="secondary" text @click="addShift">
            {{ shiftData.edit ? $t('general.save') : $t('general.create') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from 'axios'
import draggable from 'vuedraggable'
import { Swiper, SwiperSlide } from 'vue-awesome-swiper'
import helper from '~/mixins/helper'


export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  props: {
    id: {
      type: [String, Number],
      required: true,
    }
  },
  components: {
    draggable,
    Swiper,
    SwiperSlide
  },

  data () {
    return {
      dialog: false,
      date: '',
      menu: false,
      time: {
        start: false,
        end: false
      },
      tickLabels: [],
      schedData: {
        status: 0
      },
      locations: [],
      shiftDefaults: {
          id: null,
          date: '',
          start: '08:00',
          end: '08:00',
          location: 1,
          participants: [1, 2],
          edit: false
      },
      shiftData: [],
      days7: [
        { name: "Mon", 
          id:0, 
          date: '',
          list: [] 
        },
        { name: "Tue", 
          id:1, 
          date: '',
          list: [] 
        },
        { name: "Wed", 
          id:2, 
          date: '',
          list: [] 
        },
        { name: "Thu", 
          id:3, 
          date: '',
          list: [] 
        },
        { name: "Fri", 
          id:4, 
          date: '',
          list: [] 
        },
        { name: "Sat", 
          id:5, 
          date: '',
          list: [] 
        },
        { name: "Sun", 
          id:6, 
          date: '',
          list: [] 
        },        
      ],
      swiperOptions: {
        slidesPerView: 1,
        spaceBetween: 10,
        freeMode: false,
        allowTouchMove: true,
        keyboard: {
          enabled: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev'
        },
        breakpoints: {
          640: {
            slidesPerView: 2,
            spaceBetween: 20,
            allowTouchMove: false,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 30,
            allowTouchMove: false,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 40,
            allowTouchMove: false,
          },
          1366: {
            slidesPerView: 5,
            spaceBetween: 40,
            allowTouchMove: false,
          }
        }
      },

    }
  },
  
  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: this.$vuetify.breakpoint.xs,
        ghostClass: "ghost"
      }
    },
    
    timeFormat () {
      const localeTime = this.$dayjs().localeData().longDateFormat('LT')
      const isAmPm = localeTime.indexOf('A') >= 0
      console.log(localeTime)
      return (isAmPm ? 'ampm' : '24hr')
    }
  },

  created () {
    this.initialize()
  },

  methods: {
    initialize () {
      this.getSchedData()
      this.getLocations()
      this.makeStatusLabels()
      this.shiftData = this.lodash.cloneDeep(this.shiftDefaults)
    },

    async getSchedData () {
      await axios.get('/api/schedules/show/' + this.id)
        .then(response => {
          this.schedData = response.data
          this.date = this.$dayjs(this.schedData.date_start)
          this.shiftDefaults.end = this.$dayjs(this.shiftDefaults.start, 'HH:mm').add(this.team.default_shift_minutes, 'minutes').format("HH:mm")
          this.shiftDefaults.participants = [this.team.default_participants_min, this.team.default_participants_max]
          this.getShiftData(response.data.date_start)
        })

    },

    async getShiftData (date) {
      await axios.get('/api/schedules/' + this.id + '/shifts')
        .then(response => {
          // Loop through each day, show shifts
          this.days7.forEach ((item) => {
            item.date = this.$dayjs(date).add(item.id, 'd').format('YYYY-MM-DD')
            item.list = response.data.filter(shift => shift.time_start.includes(item.date))
          })
        })

    },

    async getLocations () {
      await axios.get('/api/teams/' + this.team.id + '/locations')
        .then(response => {
          this.locations = response.data
          this.shiftDefaults.location = response.data.filter(location => location.default)[0].id
        })
    },

    makeStatusLabels () {
      this.scheduleStatus.forEach((obj) => {
        this.tickLabels.push(obj.text)
      })
    },

    async updateScheduleStatus () {
        await axios({
          method: 'post',      
          url: '/api/schedules/' + this.id + '/status/',
          data: {
            status: this.schedData.status
          }
        })
        .then(response => {

        })
    },

    editAssignments () {
      this.$router.push({
          name: 'schedules.assignments',
          params: {
              id: this.id
          }
      }) 
    },

    close () {
      this.shiftData = this.lodash.cloneDeep(this.shiftDefaults)
      this.dialog = false
    },

    showShiftDialog (data, isEdit) {

      if (!isEdit) {
        this.shiftData = this.lodash.cloneDeep(this.shiftDefaults)
        this.shiftData.date = this.$dayjs(data.date).format("YYYY-MM-DD")
      } else {
          this.shiftData.id = data.id
          this.shiftData.date = this.$dayjs(data.time_start).format("YYYY-MM-DD")
          this.shiftData.start = this.$dayjs(data.time_start).format("HH:mm")
          this.shiftData.end = this.$dayjs(data.time_end).format("HH:mm")
          this.shiftData.location = data.location_id
          this.shiftData.participants = [data.min_participants, data.max_participants]
      }

      this.shiftData.edit = isEdit
      this.dialog = true
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


    async moveShift (evt) {
      var newDayID = evt.to.id
      var shiftID = evt.item.id
      var newShiftDate = this.days7[newDayID].date
      var newShiftData = []

      await axios.get('/api/schedules/' + this.id + '/shifts/' + shiftID)
        .then(response => {
          newShiftData = response.data

          var tempStart = this.$dayjs(newShiftDate + ' ' + this.$options.filters.formatTime(newShiftData.time_start)).format('YYYY-MM-DD HH:mm:ss')
          var tempEnd = this.$dayjs(newShiftDate + ' ' + this.$options.filters.formatTime(newShiftData.time_end)).format('YYYY-MM-DD HH:mm:ss')

          newShiftData.time_start = tempStart
          newShiftData.time_end = tempEnd

          this.updateShift(newShiftData)
        })
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
    

    async duplicateShift (id) {
      var newShiftData = []

      await axios.get('/api/schedules/' + this.id + '/shifts/' + id)
        .then(response => {
          newShiftData = response.data

          const formData = new FormData()
          formData.append('location_id', newShiftData.location_id)
          formData.append('time_start', newShiftData.time_start)
          formData.append('time_end', newShiftData.time_end)
          formData.append('min_participants', newShiftData.min_participants)
          formData.append('max_participants', newShiftData.max_participants)
          axios.post('/api/schedules/' + this.id + '/shifts', formData)
            .then(response => {
              this.getShiftData(this.date)
            })
        })
    },
    
    async deleteShift (id) {
      if (confirm(this.$t('schedules.confirm_delete_shift'))) {
        await axios.delete('/api/schedules/' + this.id + '/shifts/' + id)
          .then(response => {
            this.showSnackbar(this.$t('schedules.success_delete_shift'), 'success')
            this.getShiftData(this.date)
          })

      }
    },

    allowedStep: m => m % 15 === 0,
  },

}

</script>

<style scoped>
  .day-col {
    border: 1 solid white;
  }

  .shift-body {
    font-size: 0.7em;
  }

  .swiper-container {
    width: 92%;
  }

  .no-shift {
    border: 1px dashed #ffffff;
    min-height: 150px;
    vertical-align: middle;
  }

  .handle {
    cursor: grab;
  }
</style>