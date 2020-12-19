<template>
    <v-card ref="mainDiv" :id="shift.id" class="mt-5 handle" :color="shift.location.color_code">
      <v-icon :style="mandatoryIcon" color="white">
        {{ shift.mandatory ? 'mdi-heart' : 'mdi-heart-outline' }}
      </v-icon>
      
      <v-card-title class="justify-center text-h6">
        {{ shift.location.name }}
      </v-card-title>


      <v-card-subtitle class="text-center font-weight-bold">
        <div v-if="!isTemplate">{{ $dayjs(shift.time_start).format('ddd, L') }}</div>
        <div>{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</div>
      </v-card-subtitle>

      <v-card-text class="text-center pa-0">
        <v-row dense>
          <v-col cols=3 offset=3 class="pa-0">
            <v-chip small>{{ shift.min_participants }}</v-chip><br>
            <span>{{ $t('general.min') }}</span>
          </v-col>
          <v-col cols=3 class="pa-0">
            <v-chip small>{{ shift.max_participants }}</v-chip><br>
            <span>{{ $t('general.max') }}</span>
          </v-col>
        </v-row>
      </v-card-text>
      
      <v-divider class="pa-0 ma-0" />

      <v-card-actions class="pa-0">
        <v-row dense>
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  @click="showShiftDialog()"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('schedules.new_shift') }}</span>
            </v-tooltip>
          </v-col>
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  @click="deleteShift(shift.id)"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('general.delete') }}</span>
            </v-tooltip>
          </v-col>
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  @click="duplicateShift(shift.id, true)"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon>mdi-clipboard-arrow-down</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('shifts.make_next_shift') }}</span>
            </v-tooltip>
          </v-col>        
          <v-col>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn 
                  icon 
                  @click="duplicateShift(shift.id, false)"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon>mdi-content-duplicate</v-icon>
                </v-btn>
              </template>
              <span>{{ $t('general.duplicate') }}</span>
            </v-tooltip>
          </v-col>
        </v-row>
      </v-card-actions>



      <v-dialog :value="dialog" @click:outside="closeShiftDialog()" width="500px">
        <ShiftNewCard :shift="shift" :schedule="schedule" edit v-on:update="$emit('update', $event)" v-on:close="closeShiftDialog()" />
      </v-dialog>
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
    onlyinfo: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      dialog: false,
      mandatoryIcon: {
        position: 'absolute',
        top: '15px',
        left: '15px',
        zIndex: '501'
      }
    }
  },

  computed: {
    isTemplate () {
      return this.schedule.status == 9
    },
  },

  methods: {

    async duplicateShift (id, makeSubsequent) {
      var newShiftData = []

      await axios.get('/api/schedules/' + this.schedule.id + '/shifts/' + id)
        .then(response => {
          newShiftData = response.data

          // This will make the new shift immediately follow the source shift
          if (makeSubsequent) {
            newShiftData.time_start = newShiftData.time_end
            newShiftData.time_end = this.$dayjs(newShiftData.time_start).add(this.team.default_shift_minutes, 'm').format('YYYY-MM-DD HH:mm:ss')
          }

          const formData = new FormData()
          formData.append('location_id', newShiftData.location_id)
          formData.append('time_start', newShiftData.time_start)
          formData.append('time_end', newShiftData.time_end)
          formData.append('min_participants', newShiftData.min_participants)
          formData.append('max_participants', newShiftData.max_participants)
          axios.post('/api/schedules/' + this.schedule.id + '/shifts', formData)
            .then(response => {
              this.$emit('update', response.data.schedule)
            })
        })
    },
    
    async deleteShift (id) {
      if (await this.$root.$confirm(this.$t('schedules.confirm_delete_shift'), null, 'error')) {
        await axios.delete('/api/schedules/' + this.schedule.id + '/shifts/' + id)
          .then(response => {
            this.showSnackbar(this.$t('schedules.success_delete_shift'), 'success')
            this.$emit('update', response.data.schedule)
          })

      }
    },

    showShiftDialog () {
      this.dialog = true
    },

    closeShiftDialog () {
      this.dialog = false
    },

  }
}
</script>