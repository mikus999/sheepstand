<template>
  <v-card width="100%">
    <v-card-title>
      <v-icon left>{{ isTemplate ? icons.mdiCalendarStar : icons.mdiCalendarWeek }}</v-icon>
      {{ isTemplate ? $t('schedules.templates') : $t('schedules.weekly_schedules') }}
    </v-card-title>

    <v-card-text>
      <v-row>
        <v-col xs=1 sm=4 class="text-left" >
          <v-btn text class="mr-auto" :x-large="$vuetify.breakpoint.smAndUp" @click="$router.go(-1)">
            <v-icon left>{{ icons.mdiArrowLeft }}</v-icon>
            <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('general.go_back')}}</span>
          </v-btn>
        </v-col>

        <v-col xs=10 sm=4 class="text-center">
          <div v-if="!isTemplate">
            <div class="text-h6 mx-auto">{{ $t('schedules.week_of')}} {{ schedule.date_start | formatDate }}</div>
            <div class="mx-auto font-weight-bold">
              <span :class="getStatusColor()">{{ getStatusText() }}</span>
            </div>
          </div>
          <div v-else>
            <div class="text-h6 mx-auto">{{ schedule.template_name }}</div>
          </div>
        </v-col>
        
        <v-col xs=1 sm=4 class="text-right">
        </v-col>
      </v-row>

      <v-row class="my-5 mx-2">
        <div class="swiper-button-prev" v-if="$vuetify.breakpoint.smAndUp"></div>
        <div class="swiper-button-next" v-if="$vuetify.breakpoint.smAndUp"></div>      

        <v-col cols=12 sm=6 v-if="!isTemplate">
          <v-subheader class="pa-0">{{ $t('schedules.status') }}</v-subheader>

          <v-switch 
            v-model="sw_status_visible" 
            :label="$t('schedules.schedule_visible')" 
            @click="updateScheduleStatus" 
            hide-details 
            :disabled="sw_status_archive"
            class="mt-0"
          />

          <v-switch 
            v-model="sw_status_closed" 
            :label="$t('schedules.schedule_closed')" 
            @click="updateScheduleStatus" 
            hide-details 
            :disabled="!sw_status_visible || sw_status_archive"
          />

          <v-switch 
            v-model="sw_status_archive" 
            :label="$t('schedules.schedule_archive')" 
            @click="updateScheduleStatus" 
            hide-details 
          />
        </v-col>

        <v-col cols=12 :sm="!isTemplate ? '3' : '12'">
          <v-subheader class="pa-0">{{ $t('general.sort_by') }}</v-subheader>
          <v-radio-group v-model="sort_options" class="my-0" @change="parseSchedule()">
            <v-radio :label="$t('shifts.shift_time')" value="time_start" />
            <v-radio :label="$t('shifts.location')" value="location" />
          </v-radio-group>
        </v-col>

        <v-col cols=12 sm=3>
          <v-btn
            color="deep-orange"
            block
            class="my-2"
            @click="approveAllRequests(0)"
            v-if="hasPendingAssignments"
          >
            <v-icon small left>{{ icons.mdiThumbUp }}</v-icon>
            <span>{{ $vuetify.breakpoint.xs ? $t('general.all') : $t('schedules.assignments') }}</span>
          </v-btn>

          <v-btn
            color="grey"
            block
            class="my-2"
            @click="approveAllRequests(1)"
            v-if="hasPendingRequests"
          >
            <v-icon small left>{{ icons.mdiThumbUp }}</v-icon>
            <span>{{ $vuetify.breakpoint.xs ? $t('general.all') : $t('schedules.requests') }}</span>
          </v-btn>
        </v-col>
      </v-row>

      <v-row>
        <v-col>
          <swiper ref="mySwiper" :options="swiperOptions">
            <swiper-slide v-for="day in days7" :key="day.id" class="text-center">
              <h4>{{ day.date | formatWeekdayShort }}</h4>
              <h6 v-if="!isTemplate">{{ day.date | formatDate }}</h6>

              <draggable class="list-group" tag="transition-group" v-model="day.list" v-bind="dragOptions" 
                @end="moveShift" draggable=".shift" :id="day.id" handle=".handle" :disabled="isMobile">

                  <ShiftEditCard 
                    v-for="shift in day.list" 
                    :id="shift.id"
                    :key="shift.id" 
                    :shift="shift" 
                    :team_availability="team_availability"
                    v-on:update="parseSchedule()" 
                    v-on:location="showLocationOverlay(shift)"
                    class="shift"
                  />                  

                  <!-- Show the 'Add New Shift' placeholder at the top of each day -->            
                  <v-card slot="header" class="mt-5 text-center" key="footer" @click.stop="showShiftDialog(day)">
                    <v-card-text class="text-center pa-0">
                      <v-icon large class="pa-4">{{ icons.mdiPlusBox }}</v-icon>
                    </v-card-text>
                  </v-card>

                  <!-- Show placeholder card if there are now shifts for this day -->
                  <v-card slot="footer" v-if="day.list.length === 0" class="no-shift d-flex align-center mt-5" :key="day.id">
                    <v-card-text class="text-center pa-0">
                      <v-icon large class="pa-4">{{ icons.mdiSelectPlace }}</v-icon>
                    </v-card-text>
                  </v-card>

              </draggable>
            </swiper-slide>
          </swiper>
        </v-col>
      </v-row>
    </v-card-text>

    <v-divider />

    <v-card-actions>

      <v-row>
        <v-col cols=12 class="text-right">
          <v-btn
            color="error"
            text
            :block="$vuetify.breakpoint.xs"
            @click="deleteSched"
          >
            {{ isTemplate ? $t('schedules.delete_template') : $t('schedules.delete_schedule') }}
          </v-btn>

          <v-btn 
            v-if="!isTemplate"
            color="secondary" 
            :block="$vuetify.breakpoint.xs"
            @click="openSaveTemplateDialog"
          >
            {{ $t('schedules.save_as_template') }}
          </v-btn> 
        </v-col>
      </v-row>
    </v-card-actions>


        <!-- NEW SHIFT DIALOG -->
    <v-dialog :value="dialog" @click:outside="closeShiftDialog()" width="500px">
      <ShiftNewCard :shift="shift" v-on:update="parseSchedule()" v-on:close="closeShiftDialog()" :key="keyShiftNewCard"/>
    </v-dialog>


    <!-- LOCATION OVERLAY -->
    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'" z-index="505">
      <Leaflet :location="locShift.location" :width="mapWidth" height="500px" readonly 
          v-on:close="locationOverlay = false" v-on:click.native.stop/>
    </v-overlay>


    <!-- NEW TEMPLATE DIALOG -->
    <v-dialog v-model="dialog2" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">
            {{ $t('schedules.new_template') }}
          </span>
        </v-card-title>

        <v-card-text>
          <v-text-field 
            v-model="newTemplateName" 
            :label="$t('schedules.template_name')" 
            :prepend-icon="icons.mdiFormTextbox"
          ></v-text-field>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" text @click="closeSaveTemplateDialog()">{{ $t('general.cancel') }}</v-btn>
          <v-btn color="primary" @click="saveAsTemplate()">{{ $t('general.create') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-card>

</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import draggable from 'vuedraggable'
import { Swiper, SwiperSlide } from 'vue-awesome-swiper'
import { helper, scheduling } from '~/mixins/helper'
import ShiftEditCard from '~/components/ShiftEditCard.vue'
import ShiftNewCard from '~/components/ShiftNewCard.vue'
import Leaflet from '~/components/Leaflet.vue'
import cloneDeep from 'lodash/cloneDeep'

export default {
  mixins: [helper, scheduling],
  components: {
    draggable,
    Swiper,
    SwiperSlide,
    ShiftEditCard,
    ShiftNewCard,
    Leaflet
  },
  props: {
    team_availability: {
      type: [Object, Array]
    }
  },

  data () {
    return {
      shiftOverlay: false,
      locationOverlay: false,
      dialog: false,
      dialog2: false,
      date: '',
      menu: false,
      time: {
        start: false,
        end: false
      },
      tickLabels: [],
      shift: [],
      locShift: [],
      osDetails: {},
      sw_status_visible: false,
      sw_status_closed: false,
      sw_status_archive: false,
      sort_options: 'location',
      keyShiftNewCard: 0,
      newTemplateName: null,
      shiftDefaults: {
          id: null,
          time_start: null,
          time_end: null,
          min_participants: 2,
          max_participants: 3,
          location_id: null,
          mandatory: false
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
        mousewheel: {
          forceToAxis: true,
          invert: true,
        },
        keyboard: {
          enabled: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev'
        },
        breakpoints: {
          600: {
            slidesPerView: 'auto',
            spaceBetween: 20,
            allowTouchMove: this.isMobile,
            freeMode: true,
          },
          /*
          850: {
            slidesPerView: 3,
            spaceBetween: 30,
            allowTouchMove: false,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 30,
            allowTouchMove: false,
          },
          1500: {
            slidesPerView: 5,
            spaceBetween: 40,
            allowTouchMove: false,
          }
          */
        }
      },

    }
  },
  
  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      team_users: 'scheduling/team_users',
    }),

    dragOptions() {
      return {
        animation: 200,
        group: "description",
        ghostClass: "ghost"
      }
    },
    
    timeFormat () {
      const localeTime = this.$dayjs().localeData().longDateFormat('LT')
      const isAmPm = localeTime.indexOf('A') >= 0
      return (isAmPm ? 'ampm' : '24hr')
    },

    isTemplate () {
      return this.schedule.status == 9
    },

    isMobile() {
      var osDetails = this.getOS()
      return osDetails.mobile
    },

    mapWidth() {
      return this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
    },

    hasPendingAssignments() {
      var result = this.shifts.filter(s => s.users.filter(u => u.pivot.status == 0).length > 0)
      return result.length > 0
    },

    hasPendingRequests() {
      var result = this.shifts.filter(s => s.users.filter(u => u.pivot.status == 1).length > 0)
      return result.length > 0
    },
  },

  created () {
    this.initialize()
  },

  methods: {
    async initialize () {
      this.parseSchedule()
      this.shift = cloneDeep(this.shiftDefaults)
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

      this.sortShifts()


      // Set schedule status switches
      const status = this.schedule.status
      this.sw_status_archive = (status == 3)
      this.sw_status_visible = (status > 0) && !this.sw_status_archive
      this.sw_status_closed = (status > 1) && !this.sw_status_archive
    },


    sortShifts() {
      this.days7.forEach ((item) => {
        item.list.sort((a,b) => {
          if (this.sort_options == 'location') {
            var result = a.location_id - b.location_id
            if (result == 0) {
              return this.$dayjs(a.time_start).isBefore(this.$dayjs(b.time_start)) ? -1 : 1
            } else {
              return result
            }

          } else if (this.sort_options == 'time_start') {
            return this.$dayjs(a.time_start).isBefore(this.$dayjs(b.time_start)) ? -1 : 1
          }
        })
      })
    },


    async deleteSched () {
      const confirm_msg = this.isTemplate ? this.$t('schedules.confirm_delete_template') : this.$t('schedules.confirm_delete_schedule')
      const success_msg = this.isTemplate ? this.$t('schedules.success_delete_template') : this.$t('schedules.success_delete_schedule')

      if (await this.$root.$confirm(confirm_msg, null, 'error')) {
        await axios.delete('/api/schedules/' + this.schedule.id)
          .then(response => {
            this.showSnackbar(success_msg, 'success')
            this.$router.push({ name: 'schedules.index'})
          })
      }
    },


    getStatusColor() {
      var textColor = "black--text"
      if (this.getScheduleStatus(this.schedule.status) != undefined) {
        textColor = this.getScheduleStatus(this.schedule.status).color + '--text'
      }
      return textColor
    },

    getStatusText() {
      var textString = null
      if (!this.isTemplate) {
        textString = this.getScheduleStatus(this.schedule.status).text
      } else {
        textString = this.schedule.template_name
      }
      return textString
    },

    async updateScheduleStatus () {
        // Calculate status from switches
        var status = null
        if (!this.sw_status_visible) {
          status = 0
          this.sw_status_closed = false
        } else if (this.sw_status_visible && !this.sw_status_closed) {
          status = 1
        } else if (this.sw_status_visible && this.sw_status_closed) {
          status = 2
        } else {
          status = 4
        }

        if (this.sw_status_archive) {
          this.sw_status_visible = false
          this.sw_status_closed = false
          status = 3
        }


        await axios({
          method: 'post',      
          url: '/api/schedules/' + this.schedule.id + '/status/',
          data: {
            status: status
          }
        })
        .then(response => {
          this.storeSchedule(response.data.data.schedule)
        })
    },

    editAssignments () {
      this.$router.push({
          name: 'schedules.assignments',
          params: {
              id: this.schedule.id
          }
      }) 
    },


    showShiftDialog (data) {
      this.keyShiftNewCard += 1
      this.shift.time_start = this.$dayjs(data.date).format('YYYY-MM-DD') + ' 08:00'
      this.shift.time_end = this.$dayjs(this.shift.time_start).add(this.team.default_shift_minutes, 'm')
      this.dialog = true
    },


    closeShiftDialog () {
      this.shift = cloneDeep(this.shiftDefaults)
      this.dialog = false
    },


    async moveShift (evt) {
      var newDayID = evt.to.id
      var shiftID = evt.item.id
      var newShiftDate = this.days7[newDayID].date
      var newShiftData = []

      await axios.get('/api/schedules/' + this.schedule.id + '/shifts/' + shiftID)
        .then(response => {
          newShiftData = response.data.data.shift

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
        url: '/api/schedules/' + this.schedule.id + '/shifts/' + data.id,
        data: {
          location_id: data.location_id,
          time_start: data.time_start,
          time_end: data.time_end,
          min_participants: data.min_participants,
          max_participants: data.max_participants,
          mandatory: data.mandatory
        }
      })
      .then(response => {
        this.storeSchedule(response.data.data.schedule)
        this.parseSchedule()
      })
    },
    
    showShiftOverlay(shift) {
      this.shiftOverlay = true
    },


    openSaveTemplateDialog() {
      this.dialog2 = true
    },

    closeSaveTemplateDialog() {
      this.newTemplateName = null
      this.dialog2 = false
    },

    showLocationOverlay(shift) {
      if (shift.location.map != null) {
        this.locShift = shift
        this.locationOverlay = true
      }
    },

    async saveAsTemplate() {
      if (this.newTemplateName) {
        await axios({
          method: 'post',      
          url: '/api/schedules/' + this.schedule.id + '/templates/make',
          data: {
            template_name: this.newTemplateName
          }
        })
        .then(response => {
          this.showSnackbar(this.$t('schedules.success_create_template'), 'success')
          this.closeSaveTemplateDialog()
        })
      }
    },


    async approveAllRequests(status) {
      var confirm_msg = null

      if (status == 0) {
        confirm_msg = this.$t('schedules.confirm_approve_all_assignments')
      } else if (status == 1) {
        confirm_msg = this.$t('schedules.confirm_approve_all_requests')
      }

      if (await this.$root.$confirm(confirm_msg, null, 'error')) {
        await axios({
          method: 'get',      
          url: '/api/schedules/' + this.schedule.id + '/approveall/' + status,
        })
        .then(response => {
          this.storeSchedule(response.data.data.schedule)
          this.storeShifts(response.data.data.schedule.shifts)
          this.showSnackbar(this.$t('general.info_updated'), 'success')
          this.parseSchedule()
        })
        .catch(error => {
          console.log(error)
        })
      }
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

  .swiper-slide {
    width: 220px;
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