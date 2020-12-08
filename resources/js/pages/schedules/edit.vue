<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('schedules.schedule')"></PageTitle>
    </v-row>

    <v-card width="100%">
      <v-card-text>
        <v-row>
          <v-col xs=1 sm=4 class="text-left" >
            <v-btn text class="mr-auto" :x-large="$vuetify.breakpoint.smAndUp" @click="$router.go(-1)">
              <v-icon left>mdi-arrow-left</v-icon>
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
            <v-btn text class="ml-auto" :x-large="$vuetify.breakpoint.smAndUp" @click="editAssignments" v-if="!isTemplate">
              <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('schedules.assignments') }}</span>
              <v-icon right>mdi-arrow-right</v-icon>
            </v-btn>
          </v-col>
        </v-row>

        <v-row class="my-5 mx-2">
          <div class="swiper-button-prev" v-if="$vuetify.breakpoint.smAndUp"></div>
          <div class="swiper-button-next" v-if="$vuetify.breakpoint.smAndUp"></div>      

          <v-col v-if="!isTemplate">
            <v-switch 
              v-model="sw_status_visible" 
              :label="$t('schedules.schedule_visible')" 
              @click="updateScheduleStatus" 
              hide-details 
              :disabled="sw_status_archive"
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
        </v-row>

        <v-row>
          <v-col>
            <swiper ref="mySwiper" :options="swiperOptions">
              <swiper-slide v-for="day in days7" :key="day.id" class="text-center">
                <h4>{{ day.date | formatWeekdayShort }}</h4>
                <h6 v-if="!isTemplate">{{ day.date | formatDate }}</h6>

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
      </v-card-text>

      <v-divider />

      <v-card-actions>
        <v-spacer />
        <v-btn
          color="error"
          text
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
      </v-card-actions>
    </v-card>

    <!-- NEW SHIFT DIALOG -->
    <v-dialog :value="dialog" @click:outside="closeShiftDialog()" width="500px">
      <ShiftNewCard :shift="shift" :schedule="schedule" v-on:update="updateSchedule($event)" v-on:close="closeShiftDialog()" :key="keyShiftNewCard"/>
    </v-dialog>


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
            prepend-icon="mdi-form-textbox"
          ></v-text-field>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" text @click="closeSaveTemplateDialog()">{{ $t('general.cancel') }}</v-btn>
          <v-btn color="primary" @click="saveAsTemplate()">{{ $t('general.create') }}</v-btn>
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
      dialog2: false,
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
      sw_status_visible: false,
      sw_status_closed: false,
      sw_status_archive: false,
      keyShiftNewCard: 0,
      newTemplateName: null,
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
          1500: {
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
    },

    isTemplate () {
      return this.schedule.status == 9
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


          // Set schedule status switches
          const status = response.data.status
          this.sw_status_archive = (status == 3)
          this.sw_status_visible = (status > 0) && !this.sw_status_archive
          this.sw_status_closed = (status > 1) && !this.sw_status_archive
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


    makeStatusLabels () {
      this.scheduleStatus.forEach((obj) => {
        this.tickLabels.push(obj.text)
      })
    },

    getStatusColor() {
      var textColor = "black--text"
      if (this.scheduleStatus[this.schedule.status] != undefined) {
        textColor = this.scheduleStatus[this.schedule.status].color + '--text'
      }
      return textColor
    },

    getStatusText() {
      var textString = null
      if (!this.isTemplate) {
        textString = this.scheduleStatus[this.schedule.status].text
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

        this.schedule.status = status

        await axios({
          method: 'post',      
          url: '/api/schedules/' + this.id + '/status/',
          data: {
            status: status
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
      this.keyShiftNewCard += 1
      this.shift.time_start = this.$dayjs(data.date).format('YYYY-MM-DD') + ' 08:00'
      this.shift.time_end = this.$dayjs(this.shift.time_start).add(this.team.default_shift_minutes, 'm')
      this.dialog = true
    },


    closeShiftDialog () {
      this.shift = this.lodash.cloneDeep(this.shiftDefaults)
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


    openSaveTemplateDialog() {
      this.dialog2 = true
    },

    closeSaveTemplateDialog() {
      this.newTemplateName = null
      this.dialog2 = false
    },

    async saveAsTemplate() {
      if (this.newTemplateName) {
        await axios({
          method: 'post',      
          url: '/api/schedules/' + this.id + '/templates/make',
          data: {
            template_name: this.newTemplateName
          }
        })
        .then(response => {
          this.showSnackbar(this.$t('schedules.success_create_template'), 'success')
          this.closeSaveTemplateDialog()
        })
      }
    }

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