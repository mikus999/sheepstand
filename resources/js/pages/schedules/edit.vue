<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        Schedule: {{ schedData.date_start }}
      </h1>
    </v-row>

    <v-row class="mt-5 mb-5">
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </v-row>

    <v-row>
      <swiper ref="mySwiper" :options="swiperOptions">
        <swiper-slide v-for="day in days7" :key="day.id" class="text-center">
          <h4>{{ day.name }}</h4>
          <h6>{{ day.date }}</h6>

          <draggable class="list-group" tag="transition-group" v-model="day.list" v-bind="dragOptions" 
            @end="moveShift" draggable=".shift" :id="day.id">
              <v-card v-for="shift in day.list" :key="shift.id" :id="shift.id" class="shift mt-5" :color="shift.location.color_code">
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
                      <span class="text-caption">min</span>
                    </v-col>
                    <v-col cols=2>
                      <v-chip x-small>{{ shift.max_participants }}</v-chip><br>
                      <span class="text-caption">max</span>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>

              <v-card slot="header" class="mt-5 text-center" key="footer" @click.stop="showShiftDialog(day)">
                <v-card-text class="text-center pa-0">
                  <v-icon large class="pa-4">mdi-plus-box</v-icon>
                </v-card-text>
              </v-card>
          </draggable>
        </swiper-slide>
      </swiper>
    </v-row>


     <v-dialog v-model="dialog" max-width="500px">
        <v-card>
          <v-card-title class="text-center">
            <span class="headline">New Shift - {{ shiftData.date | formatDate }}</span>
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
                  <VueCtkDateTimePicker v-model="shiftData.start" id="timepickStart" 
                    only-time no-header no-label no-clear-button no-button
                    format="HH:mm" formatted="HH:mm" minute-interval="15"  
                    :dark="this.$vuetify.theme.dark" class="text-center" />
                </v-col>
                <v-col cols=5>
                  <VueCtkDateTimePicker v-model="shiftData.end" id="timepickEnd" 
                    only-time no-header no-label no-clear-button no-button
                    format="HH:mm" formatted="HH:mm" minute-interval="15" 
                    :dark="this.$vuetify.theme.dark" />
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
            <v-btn color="secondary" text @click="close">Cancel</v-btn>
            <v-btn color="secondary" text @click="addShift">Create</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import Form from 'vform'
import draggable from 'vuedraggable'
import moment from 'moment'
import { Swiper, SwiperSlide } from 'vue-awesome-swiper'
import helper from '../../mixins/helper'


export default {
  middleware: 'auth',
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
      schedData: [],
      locations: [],
      shiftDefaults: {
          date: '',
          start: '08:00',
          end: '10:00',
          location: 1,
          participants: [1, 2]
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
        allowTouchMove: false,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev'
        },
        keyboard: {
          enabled: true,
        },
        breakpoints: {
          640: {
            slidesPerView: 2,
            spaceBetween: 20,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 30,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 40,
          },
          1366: {
            slidesPerView: 5,
            spaceBetween: 40,
          }
        }
      },

    }
  },
  
  created () {
    this.initialize()
  },

  methods: {
    initialize () {
      this.getSchedData()
      this.getLocations()
      this.shiftData = this.lodash.cloneDeep(this.shiftDefaults)
    },

    async getSchedData () {
      await axios.get('/api/schedules/' + this.id)
        .then(response => {
          this.schedData = response.data
          this.date = moment(this.schedData.date_start).format("YYYY-MM-DD")

          this.getShiftData(response.data.date_start)
        })

    },

    async getShiftData (date) {
      await axios.get('/api/schedules/' + this.id + '/shifts')
        .then(response => {

          // Loop through each day, show shifts
          this.days7.forEach ( function(item) {
            item.date = moment(date).add(item.id, 'd').format("YYYY-MM-DD")
            item.list = response.data.filter(shift => shift.time_start.includes(item.date))
          })
        })

    },

    async getLocations () {
      await axios.get('/api/teams/' + this.formatJSON(this.team).id + '/locations')
        .then(response => {
          this.locations = response.data
        })
    },

    close () {
      this.dialog = false
    },

    showShiftDialog (dayInfo) {
      this.shiftData.date = moment(dayInfo.date).format("YYYY-MM-DD")
      this.dialog = true
    },

    addShift () {

      var tempStart = moment(this.shiftData.date + ' ' + this.shiftData.start).format('YYYY-MM-DD HH:mm:ss')
      var tempEnd = moment(this.shiftData.date + ' ' + this.shiftData.end).format('YYYY-MM-DD HH:mm:ss')

      if (!moment(tempStart).isBefore(moment(tempEnd))) {
        tempEnd = moment(tempStart).add(2, 'h').format('YYYY-MM-DD HH:mm:ss')
      }

      const formData = new FormData()
      formData.append('location_id', this.shiftData.location)
      formData.append('time_start', tempStart)
      formData.append('time_end', tempEnd)
      formData.append('min_participants', this.shiftData.participants[0])
      formData.append('max_participants', this.shiftData.participants[1])
      axios.post('/api/schedules/' + this.id + '/shifts', formData)
        .then(response => {
          this.getShiftData(this.date)
          this.shiftData = this.lodash.cloneDeep(this.shiftDefaults)
          this.close()  
        })
    },

    async moveShift (evt) {
      var newDayID = evt.to.id
      var shiftID = evt.item.id
      var newShiftDate = this.days7[newDayID].date
      var newShiftData = []

      await axios.get('/api/schedules/' + this.id + '/shifts/' + shiftID)
        .then(response => {
          newShiftData = response.data

          var tempStart = moment(newShiftDate + ' ' + this.$options.filters.formatTime(newShiftData.time_start)).format('YYYY-MM-DD HH:mm:ss')
          var tempEnd = moment(newShiftDate + ' ' + this.$options.filters.formatTime(newShiftData.time_end)).format('YYYY-MM-DD HH:mm:ss')

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
      })

    },    
  },


  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      }
    },

    ...mapGetters({
      user: 'auth/user',
      team: 'teams/getTeam',
    })
    
  }
}

</script>

<style scoped>
  .day-col {
    border: 1 solid white;
  }

  .shift-body {
    font-size: 0.8em;
  }

  .swiper-container {
    width: 92%;
  }
</style>