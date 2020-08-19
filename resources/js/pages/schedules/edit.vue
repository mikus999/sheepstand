<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        Schedule: {{ schedData.date_start }}
      </h1>
    </v-row>

    <v-row>
      <v-col v-for="day in days7" :key="day.id" class="custom7cols day-col" >
        <h4>{{ day.name }}</h4>

        <draggable class="list-group" tag="transition-group" v-model="day.list" v-bind="dragOptions" 
          @start="drag = true" @end="drag = false" draggable=".shift">
            <v-card v-for="shift in day.list" :key="shift.id" class="shift mt-5">
              <v-card-text class="shift-body">
                Location: {{ shift.location_id }}<br>
                Start: {{ shift.time_start | formatTime }}<br>
                End: {{ shift.time_end | formatTime }}<br>
                Max Part: {{ shift.min_participants }}<br>
                Min Part: {{ shift.max_participants }}
              </v-card-text>
            </v-card>

            <v-card slot="footer" class="mt-5" key="footer" @click.stop="showShiftDialog(day)">
              <v-card-text class="text-center">
                <v-icon large class="pa-4">mdi-plus-box</v-icon>
              </v-card-text>
            </v-card>
        </draggable>
      </v-col>
    </v-row>



     <v-dialog v-model="dialog" max-width="500px">
        <v-card>
          <v-card-title class="text-center">
            <span class="headline">New Shift - {{ shiftData.date | formatDate }}</span>
          </v-card-title>

          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="6">
                  <VueCtkDateTimePicker v-model="shiftData.start" id="timepickStart" only-time inline
                    format="HH:mm" formatted="HH:mm" minute-interval="15" input-size="lg" 
                    :dark="this.$vuetify.theme.dark" />
                </v-col>
                <v-col cols="6">
                  <VueCtkDateTimePicker v-model="shiftData.end" id="timepickEnd" only-time inline 
                    format="HH:mm" formatted="HH:mm" minute-interval="15" input-size="lg"  
                    :dark="this.$vuetify.theme.dark" />
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

export default {
  middleware: 'auth',
  layout: 'vuetify',
  props: {
    id: {
      type: String,
      required: true,
    }
  },
  components: {
    draggable
  },


  data () {
    return {
      dialog: false,
      date: '',
      menu: false,
      timetest: "08:00",
      schedData: [],
      shiftData: {
          date: '',
          start: '08:00',
          end: '10:00',
          location: 1,
          partMin: 1,
          partMax: 2
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
      ]

    }
  },

  created () {
    this.initialize()
  },

  methods: {
    initialize () {
      this.getSchedData()
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
      formData.append('min_participants', this.shiftData.partMin)
      formData.append('max_participants', this.shiftData.partMax)
      axios.post('/api/schedules/' + this.id + '/shifts', formData)
        .then(response => {
          this.getShiftData(this.date)
          this.close()  
        })
      
    }
  },

  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      };
    }
  }
}

</script>

<style scoped>
  .custom7cols {
    width: 14%;
    max-width: 14%;
    flex-basis: 14%;
  }

  .day-col {
    border: 1 solid white;
  }

  .shift-body {
    font-size: 0.8em;
  }
</style>