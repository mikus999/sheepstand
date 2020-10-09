<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('schedules.schedule')"></PageTitle>
    </v-row>

    <v-card width="100%">
      <v-row>
        <v-col xs=1 sm=4 class="text-left" >
          <v-btn text class="mr-auto" :x-large="$vuetify.breakpoint.smAndUp" @click="$router.go(-1)">
            <v-icon left>mdi-arrow-left</v-icon>
            <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('general.go_back')}}</span>
          </v-btn>
        </v-col>

        <v-col xs=10 sm=4 class="text-center">
          <span class="text-h6 mx-auto">{{ $t('schedules.week_of')}} {{ schedule.date_start | formatDate }}</span>
        </v-col>
        
        <v-col xs=1 sm=4 class="text-right">
          <v-btn text class="ml-auto" :x-large="$vuetify.breakpoint.smAndUp" @click="editAssignments" v-show="schedule.status > 0">
            <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('schedules.assignments') }}</span>
            <v-icon right>mdi-arrow-right</v-icon>
          </v-btn>
        </v-col>
      </v-row>

      <v-row class="my-5">
        <div class="swiper-button-prev" v-if="$vuetify.breakpoint.smAndUp"></div>
        <div class="swiper-button-next" v-if="$vuetify.breakpoint.smAndUp"></div>      

        <v-col>
          <v-slider v-model="schedule.status" min="0" max="2" :tick-labels="tickLabels" :color="scheduleStatus[schedule.status].color"
            ticks="always" tick-size="4" @click="updateScheduleStatus" class="mb-8">
          </v-slider>
        </v-col>
      </v-row>

      <v-row>
        <v-col>
          <swiper ref="mySwiper" :options="swiperOptions">
            <swiper-slide v-for="day in days7" :key="day.id" class="text-center">
              <h4>{{ day.date | formatWeekdayShort }}</h4>
              <h6>{{ day.date | formatDate }}</h6>

              <draggable class="list-group" tag="transition-group" v-model="day.list" v-bind="dragOptions" 
                @end="moveShift" draggable=".shift" :id="day.id" handle=".handle">

                  <ShiftEditCard v-for="shift in day.list" :key="shift.id" :shift="shift" :schedule="schedule" v-on:update="updateSchedule($event)" />                  

                  <!-- Show the 'Add New Shift' placeholder at the top of each day -->            
                  <v-card slot="header" class="mt-5 text-center" key="footer" @click.stop="showShiftDialog(day)">
                    <v-card-text class="text-center pa-0">
                      <v-icon large class="pa-4">mdi-plus-box</v-icon>
                    </v-card-text>
                  </v-card>

                  <!-- Show placeholder card if there are now shifts for this day -->
                  <v-card slot="footer" v-if="day.list.length === 0" class="no-shift d-flex align-center mt-5" :key="day.id">
                    <v-card-text class="text-center pa-0">
                      <v-icon large class="pa-4">mdi-select-place</v-icon>
                    </v-card-text>
                  </v-card>

              </draggable>
            </swiper-slide>
          </swiper>
        </v-col>
      </v-row>
    </v-card>

    <!-- NEW SHIFT DIALOG -->
    <v-dialog :value="dialog" @click:outside="closeShiftDialog()" width="500px">
      <ShiftNewCard :shift="shift" :schedule="schedule" v-on:update="updateSchedule($event)" v-on:close="closeShiftDialog()" />
    </v-dialog>

  </v-container>
</template>

<script>
import axios from 'axios'
import draggable from 'vuedraggable'
import { Swiper, SwiperSlide } from 'vue-awesome-swiper'
import helper from '~/mixins/helper'
import ShiftEditCard from '~/components/ShiftEditCard.vue'
import ShiftNewCard from '~/components/ShiftNewCard.vue'


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
    SwiperSlide,
    ShiftEditCard,
    ShiftNewCard
  },

  data () {
    return {
      shiftOverlay: false,
      dialog: false,
      date: '',
      menu: false,
      time: {
        start: false,
        end: false
      },
      tickLabels: [],
      shift: [],
      schedule: {
        status: 0
      },
      shiftDefaults: {
          id: null,
          time_start: null,
          time_end: null,
          min_participants: 2,
          max_participants: 3,
          location_id: null
      },
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
      return (isAmPm ? 'ampm' : '24hr')
    }
  },

  created () {
    this.initialize()
  },

  methods: {
    initialize () {
      this.getSchedule()
      this.makeStatusLabels()
      this.shift = this.lodash.cloneDeep(this.shiftDefaults)
    },

    async getSchedule () {
      await axios.get('/api/schedules/show/' + this.id)
        .then(response => {
          this.schedule = response.data
          this.parseSchedule()
        })

    },


    parseSchedule () {
      this.date = this.$dayjs(this.schedule.date_start)
      this.shiftDefaults.time_start = this.$dayjs(this.date).format('L') + ' 08:00'
      this.shiftDefaults.time_end = this.$dayjs(this.shiftDefaults.time_start).add(this.team.default_shift_minutes, 'm')
      this.shiftDefaults.min_participants = this.team.default_participants_min
      this.shiftDefaults.max_participants = this.team.default_participants_max

      // Loop through each day, show shifts
      this.days7.forEach ((item) => {
        item.date = this.$dayjs(this.schedule.date_start).add(item.id, 'd').format('YYYY-MM-DD')
        item.list = this.schedule.shifts.filter(shift => shift.time_start.includes(item.date))
      })
    },

    updateSchedule (sched) {
      this.schedule = sched
      this.parseSchedule()
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
            status: this.schedule.status
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


    showShiftDialog (data) {
      this.shift = this.lodash.cloneDeep(this.shiftDefaults)
      this.shift.time_start = this.$dayjs(data.date).format('L') + ' 08:00'
      this.shift.time_end = this.$dayjs(this.shift.time_start).add(this.team.default_shift_minutes, 'm')
      this.dialog = true
    },


    closeShiftDialog () {
      this.dialog = false
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
        this.schedule = response.data.schedule
        this.parseSchedule()
      })
    },
    
    showShiftOverlay(shift) {
      this.shiftOverlay = true
    },

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